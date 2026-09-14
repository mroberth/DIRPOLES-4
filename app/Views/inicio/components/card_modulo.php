<?php
/**
 * Card de módulo para el panel del Administrador.
 * Espera `$c` (una entrada de app/Config/dashboard_cards.php).
 *
 * @var array{icon:string,color:string,titulo:string,url:string,disponible:bool,stat?:string} $c
 */
$disponible = !empty($c['disponible']);
$stat       = $c['stat'] ?? null;
?>
<div class="col-xl-3 col-md-6 mb-4">
    <div class="card border-left-<?= htmlspecialchars($c['color']) ?> shadow h-100 py-2">
        <div class="card-body">
            <div class="row g-0 align-items-center">
                <div class="col me-2">
                    <div class="text-xs font-weight-bold text-<?= htmlspecialchars($c['color']) ?> text-uppercase mb-1">
                        <?= htmlspecialchars($c['titulo']) ?>
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        <?php if ($stat !== null): ?>
                            <span data-stat="<?= htmlspecialchars($stat) ?>">0</span>
                        <?php else: ?>
                            <i class="fas <?= htmlspecialchars($c['icon']) ?> text-gray-300"></i>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-auto">
                    <i class="fas <?= htmlspecialchars($c['icon']) ?> fa-2x text-gray-300"></i>
                </div>
            </div>
        </div>
        <div class="card-footer bg-white border-0 pt-0">
            <?php if ($disponible): ?>
                <a href="<?= BASE_URL . htmlspecialchars($c['url']) ?>"
                   class="btn btn-sm btn-<?= htmlspecialchars($c['color']) ?> w-100">
                    <i class="fas fa-arrow-right me-1"></i>Gestionar
                </a>
            <?php else: ?>
                <span class="badge bg-light text-secondary w-100 py-2 border">Próximamente</span>
            <?php endif; ?>
        </div>
    </div>
</div>
