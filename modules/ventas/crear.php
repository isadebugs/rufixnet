<?php
$page_title = "Nueva Venta - RUFIXNET";
include '../../includes/config.php';
include '../../includes/auth.php';
checkRole(['admin', 'ventas']);
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Obtener clientes y productos disponibles
$sql_clientes = "SELECT id, nombre_empresa FROM clientes ORDER BY nombre_empresa";
$clientes = $pdo->query($sql_clientes)->fetchAll();

$sql_productos = "SELECT p.*, pr.nombre_proveedor 
                  FROM productos p 
                  JOIN proveedores pr ON p.proveedor_id = pr.id 
                  WHERE p.activo = 1 AND p.cantidad_disponible > 0 
                  ORDER BY p.nombre";
$productos = $pdo->query($sql_productos)->fetchAll();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $cliente_id = $_POST['cliente_id'];
        $gastos_extra = floatval($_POST['gastos_extra'] ?? 0);
        $metodo_pago = $_POST['metodo_pago'];
        $precio_final = floatval($_POST['precio_final']);
        $observaciones = trim($_POST['observaciones']);
        $productos_venta = $_POST['productos'] ?? [];
        
        // Validaciones
        if (empty($cliente_id)) {
            throw new Exception("Seleccione un cliente");
        }
        
        if (empty($metodo_pago)) {
            throw new Exception("Seleccione un método de pago");
        }
        
        if (empty($productos_venta)) {
            throw new Exception("Agregue al menos un producto");
        }
        
        // Subir comprobante si existe
        $comprobante_pago = null;
        if (isset($_FILES['comprobante_pago']) && $_FILES['comprobante_pago']['error'] === UPLOAD_ERR_OK) {
            $comprobante_pago = uploadFile($_FILES['comprobante_pago'], 'comprobantes');
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Insertar venta
        $sql_venta = "INSERT INTO ventas (cliente_id, gastos_extra, metodo_pago, comprobante_pago, precio_final, observaciones, usuario_creacion) 
                      VALUES (:cliente_id, :gastos_extra, :metodo_pago, :comprobante_pago, :precio_final, :observaciones, :usuario_creacion)";
        
        $stmt = $pdo->prepare($sql_venta);
        $stmt->bindParam(':cliente_id', $cliente_id);
        $stmt->bindParam(':gastos_extra', $gastos_extra);
        $stmt->bindParam(':metodo_pago', $metodo_pago);
        $stmt->bindParam(':comprobante_pago', $comprobante_pago);
        $stmt->bindParam(':precio_final', $precio_final);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':usuario_creacion', $_SESSION['user_id']);
        $stmt->execute();
        
        $venta_id = $pdo->lastInsertId();
        
        // Insertar detalles de venta y actualizar inventario
        foreach ($productos_venta as $producto) {
            $producto_id = $producto['producto_id'];
            $cantidad = intval($producto['cantidad']);
            $precio_unitario = floatval($producto['precio_unitario']);
            
            // Insertar detalle
            $sql_detalle = "INSERT INTO detalle_ventas (venta_id, producto_id, cantidad, precio_unitario) 
                           VALUES (:venta_id, :producto_id, :cantidad, :precio_unitario)";
            $stmt = $pdo->prepare($sql_detalle);
            $stmt->bindParam(':venta_id', $venta_id);
            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':precio_unitario', $precio_unitario);
            $stmt->execute();
            
            // Actualizar inventario
            $sql_update = "UPDATE productos SET cantidad_disponible = cantidad_disponible - :cantidad WHERE id = :producto_id";
            $stmt = $pdo->prepare($sql_update);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':producto_id', $producto_id);
            $stmt->execute();
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Venta registrada correctamente";
        header("Location: index.php");
        exit();
        
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = $e->getMessage();
    }
}
?>

<main class="main-content">
    <div class="content-header">
        <h1>Nueva Venta</h1>
        <a href="index.php" class="btn btn-secondary">Volver a Ventas</a>
    </div>

    <div class="form-container">
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="ventaForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="cliente_id">Cliente *</label>
                    <select id="cliente_id" name="cliente_id" required>
                        <option value="">Seleccione un cliente</option>
                        <?php foreach ($clientes as $cliente): ?>
                            <option value="<?php echo $cliente['id']; ?>" <?php echo (isset($_POST['cliente_id']) && $_POST['cliente_id'] == $cliente['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cliente['nombre_empresa']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="gastos_extra">Gastos Extra (MXN)</label>
                    <input type="number" id="gastos_extra" name="gastos_extra" step="0.01" min="0" 
                           value="<?php echo isset($_POST['gastos_extra']) ? $_POST['gastos_extra'] : '0'; ?>">
                </div>
                
                <div class="form-group">
                    <label for="metodo_pago">Método de Pago *</label>
                    <select id="metodo_pago" name="metodo_pago" required>
                        <option value="">Seleccione método</option>
                        <option value="efectivo" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'efectivo') ? 'selected' : ''; ?>>Efectivo</option>
                        <option value="transferencia" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'transferencia') ? 'selected' : ''; ?>>Transferencia</option>
                        <option value="tarjeta_debito" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'tarjeta_debito') ? 'selected' : ''; ?>>Tarjeta Débito</option>
                        <option value="tarjeta_credito" <?php echo (isset($_POST['metodo_pago']) && $_POST['metodo_pago'] == 'tarjeta_credito') ? 'selected' : ''; ?>>Tarjeta Crédito</option>
                    </select>
                </div>
            </div>
            
            <!-- Productos -->
            <div class="form-group">
                <label>Productos de la Venta *</label>
                <div class="product-items" id="productItems">
                    <!-- Los productos se agregarán dinámicamente aquí -->
                </div>
                <button type="button" class="btn btn-secondary" id="addProduct">+ Agregar Producto</button>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="comprobante_pago">Comprobante de Pago (Imagen)</label>
                    <div class="file-drop">
                        <input type="file" id="comprobante_pago" name="comprobante_pago" accept="image/*">
                        <label for="comprobante_pago">Arrastre una imagen aquí o haga clic para seleccionar</label>
                        <div class="file-preview" id="comprobantePreview"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="precio_final">Precio Final (MXN) *</label>
                    <input type="number" id="precio_final" name="precio_final" step="0.01" min="0" required readonly
                           value="<?php echo isset($_POST['precio_final']) ? $_POST['precio_final'] : '0'; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" rows="3"><?php echo isset($_POST['observaciones']) ? htmlspecialchars($_POST['observaciones']) : ''; ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Registrar Venta</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<script>
// Productos disponibles para seleccionar
const productosDisponibles = <?php echo json_encode($productos); ?>;

document.addEventListener('DOMContentLoaded', function() {
    const productItems = document.getElementById('productItems');
    const addProductBtn = document.getElementById('addProduct');
    const precioFinalInput = document.getElementById('precio_final');
    
    // Agregar primer producto
    addProductItem();
    
    addProductBtn.addEventListener('click', function() {
        addProductItem();
    });
    
    function addProductItem() {
        const index = productItems.querySelectorAll('.product-item').length;
        const productItem = createProductItem(index);
        productItems.appendChild(productItem);
    }
    
    function createProductItem(index) {
        const div = document.createElement('div');
        div.className = 'product-item';
        div.innerHTML = `
            <div class="product-item-header">
                <h4>Producto ${index + 1}</h4>
                <button type="button" class="remove-product btn btn-sm btn-danger">Eliminar</button>
            </div>
            <div class="product-item-fields">
                <div class="form-group">
                    <label>Producto *</label>
                    <select name="productos[${index}][producto_id]" required onchange="updatePrecioUnitario(this, ${index})">
                        <option value="">Seleccione producto</option>
                        ${productosDisponibles.map(p => `
                            <option value="${p.id}" data-precio="${p.precio_unitario}" data-stock="${p.cantidad_disponible}">
                                ${p.nombre} - $${p.precio_unitario} (Stock: ${p.cantidad_disponible})
                            </option>
                        `).join('')}
                    </select>
                </div>
                <div class="form-group">
                    <label>Cantidad *</label>
                    <input type="number" name="productos[${index}][cantidad]" min="1" required onchange="calcularTotal()">
                </div>
                <div class="form-group">
                    <label>Precio Unitario (MXN) *</label>
                    <input type="number" name="productos[${index}][precio_unitario]" step="0.01" min="0" required onchange="calcularTotal()">
                </div>
                <div class="form-group">
                    <label>Subtotal</label>
                    <input type="text" class="subtotal" readonly style="background: #f3f4f6;">
                </div>
            </div>
        `;
        return div;
    }
    
    // Eliminar producto
    productItems.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-product')) {
            e.target.closest('.product-item').remove();
            updateProductIndexes();
            calcularTotal();
        }
    });
    
    function updateProductIndexes() {
        const items = productItems.querySelectorAll('.product-item');
        items.forEach((item, index) => {
            const header = item.querySelector('h4');
            header.textContent = `Producto ${index + 1}`;
            
            // Actualizar los names de los inputs
            const inputs = item.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/productos\[\d+\]/, `productos[${index}]`);
                    input.setAttribute('name', newName);
                }
            });
        });
    }
});

// Funciones globales para cálculos
function updatePrecioUnitario(select, index) {
    const precio = select.options[select.selectedIndex].getAttribute('data-precio');
    const precioInput = select.closest('.product-item-fields').querySelector('input[name$="[precio_unitario]"]');
    if (precio) {
        precioInput.value = precio;
    }
    calcularTotal();
}

function calcularTotal() {
    let total = 0;
    const gastosExtra = parseFloat(document.getElementById('gastos_extra').value) || 0;
    
    document.querySelectorAll('.product-item').forEach(item => {
        const cantidad = parseFloat(item.querySelector('input[name$="[cantidad]"]').value) || 0;
        const precio = parseFloat(item.querySelector('input[name$="[precio_unitario]"]').value) || 0;
        const subtotal = cantidad * precio;
        
        // Actualizar subtotal
        const subtotalInput = item.querySelector('.subtotal');
        subtotalInput.value = '$' + subtotal.toFixed(2);
        
        total += subtotal;
    });
    
    total += gastosExtra;
    document.getElementById('precio_final').value = total.toFixed(2);
}

// Preview de comprobante
document.getElementById('comprobante_pago').addEventListener('change', function(e) {
    previewImage(this, 'comprobantePreview');
});
</script>

<?php include '../../includes/footer.php'; ?>