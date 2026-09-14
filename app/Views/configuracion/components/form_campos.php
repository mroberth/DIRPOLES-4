<?php
/**
 * app/Views/configuracion/components/form_campos.php
 * ---------------------------------------------------------------
 * Renderiza los campos de un catálogo a partir de $cfg.
 * Reutilizado por crear.php y por los modales de consultar.php.
 *
 * @var array  $cfg
 * @var string $tipo
 * @var string $modo      'crear' | 'editar' (en editar agrega el select de estatus)
 * @var array  $servicios opciones para los selects con opciones_de = servicios
 */
$modo = $modo ?? 'crear';

foreach ($cfg['campos'] as $campo):
    $idCampo = $tipo . '_' . $campo['name'];
    $attrs = 'data-validar="1" data-label="' . htmlspecialchars($campo['label']) . '"';
    if (!empty($campo['required'])) {
        $attrs .= ' required data-required="1"';
    }
    if (!empty($campo['min'])) {
        $attrs .= ' data-min="' . (int) $campo['min'] . '"';
    }
    if (!empty($campo['max'])) {
        $attrs .= ' data-max="' . (int) $campo['max'] . '"';
    }
    if (!empty($campo['regex'])) {
        $attrs .= ' data-regex="' . htmlspecialchars($campo['regex']) . '"';
        $attrs .= ' data-regex-flags="' . htmlspecialchars($campo['regex_flags'] ?? '') . '"';
    }
    $prefijo = trim((string) ($campo['prefijo'] ?? ''));
    if ($prefijo !== '') {
        $attrs .= ' data-prefijo="' . htmlspecialchars($prefijo) . '"';
    }
    ?>
    <div class="mb-3">
        <label for="<?= htmlspecialchars($idCampo) ?>" class="form-label">
            <?= htmlspecialchars($campo['label']) ?>
            <?= !empty($campo['required']) ? '<span class="text-danger">*</span>' : '' ?>
        </label>

        <?php if (($campo['tipo'] ?? 'text') === 'textarea'): ?>
            <textarea class="form-control" id="<?= htmlspecialchars($idCampo) ?>"
                      name="<?= htmlspecialchars($campo['name']) ?>"
                      maxlength="<?= (int) ($campo['max'] ?? 255) ?>" rows="2"
                      <?= $attrs ?>></textarea>

        <?php elseif (($campo['tipo'] ?? '') === 'select'): ?>
            <select class="form-select select2" id="<?= htmlspecialchars($idCampo) ?>"
                    name="<?= htmlspecialchars($campo['name']) ?>"
                    data-placeholder="Seleccione…" <?= $attrs ?>>
                <option value="">Seleccione…</option>
                <?php if (($campo['opciones_de'] ?? '') === 'servicios'): ?>
                    <?php foreach ($servicios as $s): ?>
                        <option value="<?= (int) $s['id_servicios'] ?>"><?= htmlspecialchars($s['nombre_serv']) ?></option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach (($campo['opciones'] ?? []) as $val => $texto): ?>
                        <option value="<?= htmlspecialchars($val) ?>"><?= htmlspecialchars($texto) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>

        <?php else: ?>
            <?php if ($prefijo !== ''): ?><div class="input-group"><?php endif; ?>
            <?php if ($prefijo !== ''): ?>
                <span class="input-group-text"><?= htmlspecialchars($prefijo) ?></span>
            <?php endif; ?>
            <input type="<?= htmlspecialchars($campo['tipo'] ?? 'text') ?>"
                   class="form-control" id="<?= htmlspecialchars($idCampo) ?>"
                   name="<?= htmlspecialchars($campo['name']) ?>"
                   maxlength="<?= (int) ($campo['max'] ?? 255) ?>"
                   placeholder="<?= htmlspecialchars($campo['placeholder'] ?? '') ?>"
                   <?= $attrs ?>>
            <?php if ($prefijo !== ''): ?></div><?php endif; ?>
        <?php endif; ?>

        <div id="<?= htmlspecialchars($idCampo) ?>Error" class="form-text text-danger"></div>
    </div>
<?php endforeach; ?>

<?php if ($modo === 'editar' && !empty($cfg['con_estatus'])): ?>
    <div class="mb-3">
        <label for="<?= htmlspecialchars($tipo) ?>_estatus" class="form-label">
            <i class="fas fa-toggle-on text-primary me-1"></i> Estatus
        </label>
        <select class="form-select" id="<?= htmlspecialchars($tipo) ?>_estatus" name="estatus">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
        </select>
    </div>
<?php endif; ?>
