<?php
/**
 * Archive Header Template
 *
 * @package Inmopress\CRM
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}
?>

<header class="archive-header">
    <h1 class="archive-title"><?php post_type_archive_title(); ?></h1>
    <?php
    $description = get_the_archive_description();
    if ($description) {
        echo '<div class="archive-description">' . wp_kses_post($description) . '</div>';
    }
    ?>
</header>


