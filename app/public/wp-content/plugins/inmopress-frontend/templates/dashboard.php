<?php if (!defined('ABSPATH'))
    exit;
// Variables available: $user, $stats, $recent_activity
?>
<div class="inmopress-dashboard">
    <div class="dashboard-header" style="margin-bottom: 30px;">
        <h1>CRM Panel: <?php echo esc_html($user->display_name); ?></h1>
        <div class="dashboard-stats"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
            <div class="stat-box"
                style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="margin: 0; font-size: 2em; color: #2563eb;"><?php echo number_format($stats['inmuebles']); ?>
                </h3>
                <p style="margin: 0; color: #64748b;">Inmuebles Activos</p>
            </div>
            <div class="stat-box"
                style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="margin: 0; font-size: 2em; color: #10b981;"><?php echo number_format($stats['clientes']); ?>
                </h3>
                <p style="margin: 0; color: #64748b;">Clientes Potenciales</p>
            </div>
            <div class="stat-box"
                style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <h3 style="margin: 0; font-size: 2em; color: #f59e0b;"><?php echo number_format($stats['visitas']); ?>
                </h3>
                <p style="margin: 0; color: #64748b;">Visitas Pendientes</p>
            </div>
        </div>
    </div>

    <div class="inmopress-tabs" style="border-bottom: 2px solid #e2e8f0; margin-bottom: 30px;">
        <nav style="display: flex; gap: 20px;">
            <a href="#tab-overview" class="tab-link active"
                style="padding: 10px 0; border-bottom: 2px solid #2563eb; color: #2563eb; font-weight: bold; text-decoration: none;">Resumen</a>
            <a href="#tab-properties" class="tab-link"
                style="padding: 10px 0; border-bottom: 2px solid transparent; color: #64748b; text-decoration: none;">Inmuebles</a>
            <a href="#tab-leads" class="tab-link"
                style="padding: 10px 0; border-bottom: 2px solid transparent; color: #64748b; text-decoration: none;">Clientes</a>
            <a href="#tab-transactions" class="tab-link"
                style="padding: 10px 0; border-bottom: 2px solid transparent; color: #64748b; text-decoration: none;">Transacciones</a>
        </nav>
    </div>

    <!-- Tab Content: Overview -->
    <div id="tab-overview" class="tab-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Actividad Reciente</h2>
            <div class="quick-actions">
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('properties', array('new' => 1))); ?>" class="button button-primary">＋
                    Inmueble</a>
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('clients', array('new' => 1))); ?>" class="button">＋ Cliente</a>
                <a href="<?php echo esc_url(Inmopress_Shortcodes::panel_url('transactions', array('new' => 1))); ?>" class="button">＋ Transacción</a>
            </div>
        </div>

        <?php if (!empty($recent_activity)): ?>
            <ul style="list-style: none; padding: 0;">
                <?php foreach ($recent_activity as $activity):
                    $border_color = ($activity['type'] == 'Inmueble') ? '#0ea5e9' : '#f59e0b';
                    ?>
                    <li
                        style="background: #fff; padding: 15px; margin-bottom: 10px; border-radius: 6px; border-left: 4px solid <?php echo $border_color; ?>; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: flex; justify-content: space-between;">
                        <div>
                            <strong
                                style="display: block; color: #64748b; font-size: 0.85em;"><?php echo esc_html($activity['type']); ?></strong>
                            <a href="<?php echo esc_url($activity['link']); ?>"
                                style="color: #0f172a; text-decoration: none; font-weight: 500;">
                                <?php echo esc_html($activity['title']); ?>
                            </a>
                        </div>
                        <span style="color: #94a3b8; font-size: 0.9em;"><?php echo esc_html($activity['date']); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p style="color: #64748b;">No hay actividad reciente.</p>
        <?php endif; ?>
    </div>

    <!-- Tab Content: Properties -->
    <div id="tab-properties" class="tab-content" style="display: none;">
        <?php echo do_shortcode('[inmopress_inmuebles_list]'); ?>
    </div>

    <!-- Tab Content: Leads -->
    <div id="tab-leads" class="tab-content" style="display: none;">
        <?php echo do_shortcode('[inmopress_clientes_list]'); ?>
    </div>

    <!-- Tab Content: Transactions -->
    <div id="tab-transactions" class="tab-content" style="display: none;">
        <?php echo do_shortcode('[inmopress_transactions_list]'); ?>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabs = document.querySelectorAll('.tab-link');
        const contents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function (e) {
                e.preventDefault();

                // Remove active class from all tabs
                tabs.forEach(t => {
                    t.style.color = '#64748b';
                    t.style.borderBottomColor = 'transparent';
                    t.classList.remove('active');
                });

                // Add active class to clicked tab
                this.style.color = '#2563eb';
                this.style.borderBottomColor = '#2563eb';
                this.classList.add('active');

                // Hide all contents
                contents.forEach(c => c.style.display = 'none');

                // Show target content
                const targetId = this.getAttribute('href').substring(1);
                document.getElementById(targetId).style.display = 'block';
            });
        });

        // Check URL hash for direct tab access
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const targetTab = document.querySelector(`.tab-link[href="#${hash}"]`);
            if (targetTab) {
                targetTab.click();
            }
        }
    });
</script>
