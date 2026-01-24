<?php require_once BASE_PATH . '/utils/url_helpers.php'; ?>
<div id="dynamic-content">
    <h2>Mi Perfil</h2>
    <div class="profile-columns">
        <section class="profile-panel profile-panel-info">
            <h3>Información Personal</h3>
            <form id="update-profile-form" class="ajax-form" action="<?php echo url('participant/profile'); ?>" method="POST">
                <input type="hidden" name="action" value="update_profile">
                <div class="form-group">
                    <label for="cedula">Cédula:</label>
                    <input type="text" id="cedula" name="cedula" value="<?php echo htmlspecialchars($user['cedula']); ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo:</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($user['nombre_completo']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="correo_electronico">Correo Electrónico:</label>
                <input type="email" id="correo_electronico" name="correo_electronico" value="<?php echo htmlspecialchars($user['correo_electronico']); ?>" required>
                </div>
                <div class="form-group">
                <label for="telefono">Número de Teléfono:</label>
                <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($user['telefono']); ?>" required maxlength="10" pattern="\d{10}" inputmode="numeric" title="Debe contener exactamente 10 dígitos numéricos">
                </div>
                <button type="submit" class="btn">Actualizar Perfil</button>
            </form>
        </section>

        <section class="profile-panel profile-panel-divider profile-panel-cpcs">
            <h3>Mis CPCs</h3>
            <div class="cpcs-tools">
                <input type="search" id="user-cpcs-search" placeholder="Buscar por descripción..." autocomplete="off">
                <button type="button" id="open-cpc-modal" class="btn btn-small">CPC nuevo</button>
            </div>
            <table class="data-table" id="user-cpcs-table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="user-cpcs-body" data-page-size="6">
                    <?php foreach ($userCPCs as $cpc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cpc['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($cpc['descripcion']); ?></td>
                            <td>
                                <form class="ajax-form" action="<?php echo url('participant/profile'); ?>" method="POST">
                                    <input type="hidden" name="action" value="remove_cpc">
                                    <input type="hidden" name="cpc_id" value="<?php echo $cpc['id']; ?>">
                                    <button type="submit" class="btn btn-small btn-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div id="user-cpcs-pagination" class="pagination"></div>

            <h3>Agregar CPC</h3>
            <form id="add-cpc-form" class="ajax-form" action="<?php echo url('participant/profile'); ?>" method="POST">
                <input type="hidden" name="action" value="add_cpc">
                <select name="cpc_id" required>
                    <?php foreach ($allCPCs as $cpc): ?>
                        <?php if (!in_array($cpc, $userCPCs)): ?>
                            <option value="<?php echo $cpc['id']; ?>"><?php echo htmlspecialchars($cpc['codigo'] . ' - ' . $cpc['descripcion']); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn">Agregar CPC</button>
            </form>
        </section>
    </div>

    <div id="cpc-modal" class="cpc-modal" aria-hidden="true">
        <div class="cpc-modal-content" role="dialog" aria-modal="true" aria-labelledby="cpc-modal-title">
            <div class="cpc-modal-header">
                <h3 id="cpc-modal-title">Agregar CPCs</h3>
                <button type="button" class="cpc-modal-close" id="close-cpc-modal" aria-label="Cerrar">×</button>
            </div>
            <div class="cpc-modal-body">
                <div class="cpc-modal-tools">
                    <label for="available-cpc-search">Buscar por descripción:</label>
                    <input type="search" id="available-cpc-search" placeholder="Escribe parte de la descripción..." autocomplete="off">
                </div>
                <table class="data-table" id="available-cpcs-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Código</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody id="available-cpcs-body" data-page-size="6">
                        <?php foreach ($allCPCs as $cpc): ?>
                            <tr data-cpc-id="<?php echo htmlspecialchars($cpc['id']); ?>" data-cpc-desc="<?php echo htmlspecialchars($cpc['descripcion']); ?>">
                                <td>
                                    <input type="checkbox" class="cpc-select-checkbox" value="<?php echo htmlspecialchars($cpc['id']); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($cpc['codigo']); ?></td>
                                <td><?php echo htmlspecialchars($cpc['descripcion']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div id="available-cpcs-pagination" class="pagination"></div>
            </div>
            <div class="cpc-modal-footer">
                <button type="button" class="btn" id="add-selected-cpcs">Agregar seleccionados</button>
            </div>
        </div>
    </div>
</div>