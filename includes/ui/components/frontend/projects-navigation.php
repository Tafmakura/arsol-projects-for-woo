<?php
/**
 * Projects Navigation Component
 * 
 * Tab navigation for projects listing page.
 * Variables: $current_tab (optional)
 */

if (!defined('ABSPATH')) exit;

$current_tab = $current_tab ?? 'active';

// Define navigation tabs
$tabs = [
    'active' => [
        'label' => __('Active Projects', 'arsol-pfw'),
        'url' => wc_get_account_endpoint_url('projects'),
        'count' => 0 // Could be populated with actual counts
    ],
    'proposals' => [
        'label' => __('Proposals', 'arsol-pfw'),
        'url' => add_query_arg('tab', 'proposals', wc_get_account_endpoint_url('projects')),
        'count' => 0
    ],
    'requests' => [
        'label' => __('Requests', 'arsol-pfw'),
        'url' => add_query_arg('tab', 'requests', wc_get_account_endpoint_url('projects')),
        'count' => 0
    ]
];

?>

<nav class="arsol-projects-navigation">
    <ul class="projects-nav-tabs">
        <?php foreach ($tabs as $tab_key => $tab_data): ?>
            <li class="nav-tab-item">
                <a href="<?php echo esc_url($tab_data['url']); ?>" 
                   class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html($tab_data['label']); ?>
                    <?php if ($tab_data['count'] > 0): ?>
                        <span class="tab-count">(<?php echo intval($tab_data['count']); ?>)</span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
