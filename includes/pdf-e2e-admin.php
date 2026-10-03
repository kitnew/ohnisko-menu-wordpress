<?php

if (!defined('ABSPATH')) {
    exit;
}

function ohnisko_menu_read_jobs(): array
{
    $files = glob(ohnisko_menu_runtime_dir() . '/jobs/*.json') ?: [];
    $jobs = [];
    foreach ($files as $file) {
        $job = json_decode((string) @file_get_contents($file), true);
        if (is_array($job) && !empty($job['id'])) $jobs[] = $job;
    }
    usort($jobs, static fn(array $a, array $b): int => strcmp((string) ($b['created_at'] ?? ''), (string) ($a['created_at'] ?? '')));
    return $jobs;
}

add_action('admin_menu', function () {
    add_submenu_page('edit.php?post_type=ohnisko_menu_item', 'Menu PDF versions', 'PDF Versions', 'manage_options', 'ohnisko-menu-pdf', 'ohnisko_render_pdf_e2e_page');
});

function ohnisko_render_pdf_e2e_page(): void
{
    if (!current_user_can('manage_options')) wp_die('Forbidden');

    $runtime_dir = ohnisko_menu_runtime_dir();
    $jobs_dir = $runtime_dir . '/jobs';
    $notice = '';
    $error = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['ohnisko_generate_pdf'])) {
            check_admin_referer('ohnisko_generate_pdf');
            if (!wp_mkdir_p($jobs_dir)) wp_die('The PDF job directory could not be created.');
            $id = str_replace('-', '', wp_generate_uuid4());
            $job = ['id' => $id, 'status' => 'pending', 'filename' => $id . '.pdf', 'created_at' => gmdate(DATE_ATOM), 'attempts' => 0, 'error' => null];
            $destination = $jobs_dir . '/' . $id . '.json';
            $temporary = $jobs_dir . '/.' . $id . '.' . wp_generate_password(12, false) . '.tmp';
            $encoded = wp_json_encode($job, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $written = $encoded !== false && file_put_contents($temporary, $encoded . "\n", LOCK_EX) !== false;
            if (!$written || !chmod($temporary, 0600) || !rename($temporary, $destination)) {
                @unlink($temporary);
                wp_die('The PDF job could not be queued.');
            }
            wp_safe_redirect(add_query_arg(['page' => 'ohnisko-menu-pdf', 'job' => $id], admin_url('admin.php')));
            exit;
        }

        if (isset($_POST['ohnisko_publish_job'])) {
            $id = isset($_POST['job_id']) ? sanitize_text_field(wp_unslash($_POST['job_id'])) : '';
            check_admin_referer('ohnisko_publish_' . $id);
            $file = $jobs_dir . '/' . $id . '.json';
            $job = preg_match('/^[a-f0-9]{32}$/', $id) && is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
            if (!is_array($job) || ($job['status'] ?? '') !== 'ready' || empty($job['filename'])) {
                $error = 'Only a completed ready PDF can be published.';
            } else {
                $current = ['job_id' => $id, 'filename' => $job['filename'], 'published_at' => gmdate(DATE_ATOM)];
                update_option('ohnisko_menu_current_pdf', $current, false);
                $history = get_option('ohnisko_menu_publish_history', []);
                if (!is_array($history)) $history = [];
                $history[] = ['job_id' => $id, 'filename' => $job['filename'], 'published_at' => $current['published_at']];
                update_option('ohnisko_menu_publish_history', $history, false);
                $notice = 'The selected version is now the current public menu.';
            }
        }
    }

    $jobs = ohnisko_menu_read_jobs();
    $current = get_option('ohnisko_menu_current_pdf', []);
    $published = get_option('ohnisko_menu_publish_history', []);
    $published_at = [];
    foreach (is_array($published) ? $published : [] as $entry) {
        if (!empty($entry['job_id'])) $published_at[$entry['job_id']] = $entry['published_at'] ?? '';
    }
    $requested = isset($_GET['job']) ? sanitize_text_field(wp_unslash($_GET['job'])) : '';
    ?>
    <div class="wrap">
        <h1>Menu PDF Versions</h1>
        <?php if ($notice !== '') : ?><div class="notice notice-success"><p><?php echo esc_html($notice); ?></p></div><?php endif; ?>
        <?php if ($error !== '') : ?><div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div><?php endif; ?>
        <p>Generate creates a background job. Ready versions can be previewed, downloaded, and published separately. The public menu address stays stable at <code><?php echo esc_html(home_url('/?ohnisko_menu=1')); ?></code>.</p>
        <form method="post">
            <?php wp_nonce_field('ohnisko_generate_pdf'); ?>
            <button type="submit" name="ohnisko_generate_pdf" value="1" class="button button-primary">Generate PDF</button>
        </form>
        <?php if ($requested !== '') : ?>
            <p>Latest job <strong><?php echo esc_html($requested); ?></strong>. Refresh this page to see worker progress.</p>
        <?php endif; ?>
        <?php if (is_array($current) && !empty($current['job_id'])) : ?>
            <h2>Current published version</h2>
            <p><?php echo esc_html((string) $current['filename']); ?> · <?php echo esc_html((string) $current['published_at']); ?>
                <a class="button" href="<?php echo esc_url(home_url('/?ohnisko_menu=1')); ?>" target="_blank" rel="noopener">Preview current</a>
            </p>
        <?php endif; ?>
        <h2>Version history</h2>
        <table class="widefat striped"><thead><tr><th>Created</th><th>Version</th><th>Status</th><th>Publication history</th><th>Actions</th></tr></thead><tbody>
        <?php foreach ($jobs as $job) : ?>
            <tr>
                <td><?php echo esc_html((string) ($job['created_at'] ?? '')); ?></td>
                <td><?php echo esc_html((string) ($job['filename'] ?? $job['id'])); ?><?php if (($current['job_id'] ?? '') === ($job['id'] ?? '')) : ?> <strong>(current)</strong><?php endif; ?></td>
                <td><?php echo esc_html((string) ($job['status'] ?? 'unknown')); ?><?php if (($job['status'] ?? '') === 'failed' && !empty($job['error'])) : ?><br><span><?php echo esc_html((string) $job['error']); ?></span><?php endif; ?></td>
                <td><?php echo isset($published_at[$job['id']]) ? esc_html((string) $published_at[$job['id']]) : '—'; ?></td>
                <td>
                    <?php if (($job['status'] ?? '') === 'ready') : ?>
                        <?php $pdf_link = wp_nonce_url(add_query_arg(['action' => 'ohnisko_menu_pdf', 'job' => $job['id']], admin_url('admin-post.php')), 'ohnisko_menu_pdf_' . $job['id']); ?>
                        <a class="button" href="<?php echo esc_url($pdf_link); ?>" target="_blank" rel="noopener">Preview</a>
                        <a class="button" href="<?php echo esc_url(add_query_arg('download', '1', $pdf_link)); ?>">Download</a>
                        <?php if (($current['job_id'] ?? '') !== ($job['id'] ?? '')) : ?>
                            <form method="post" style="display:inline-block"><?php wp_nonce_field('ohnisko_publish_' . $job['id']); ?><input type="hidden" name="job_id" value="<?php echo esc_attr((string) $job['id']); ?>"><button class="button button-primary" name="ohnisko_publish_job" value="1">Publish</button></form>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($jobs === []) : ?><tr><td colspan="5">No PDF versions yet.</td></tr><?php endif; ?>
        </tbody></table>
    </div>
    <?php
}
