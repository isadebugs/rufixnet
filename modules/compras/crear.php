<?php
$page_title = "Nueva Compra - RUFIXNET";
include '../../includes/config.php';
include '../../includes/auth.php';
checkRole(['admin', 'compras']);
include '../../includes/header.php';
include '../../includes/sidebar.php';

// Obtener proveedores
$sql_proveedores = "SELECT id, nombre_proveedor FROM proveedores ORDER BY nombre_proveedor";
$proveedores = $pdo->query($sql_proveedores)->fetchAll();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $proveedor_id = $_POST['proveedor_id'];
        $precio_final = floatval($_POST['precio_final']);
        $observaciones = trim($_POST['observaciones']);
        $productos_compra = $_POST['productos'] ?? [];
        
        // Validaciones
        if (empty($proveedor_id)) {
            throw new Exception("Seleccione un proveedor");
        }
        
        if (empty($productos_compra)) {
            throw new Exception("Agregue al menos un producto");
        }
        
        // Iniciar transacción
        $pdo->beginTransaction();
        
        // Insertar compra
        $sql_compra = "INSERT INTO compras (proveedor_id, precio_final, observaciones, usuario_creacion) 
                      VALUES (:proveedor_id, :precio_final, :observaciones, :usuario_creacion)";
        
        $stmt = $pdo->prepare($sql_compra);
        $stmt->bindParam(':proveedor_id', $proveedor_id);
        $stmt->bindParam(':precio_final', $precio_final);
        $stmt->bindParam(':observaciones', $observaciones);
        $stmt->bindParam(':usuario_creacion', $_SESSION['user_id']);
        $stmt->execute();
        
        $compra_id = $pdo->lastInsertId();
        
        // Insertar detalles de compra
        foreach ($productos_compra as $producto) {
            $nombre_producto = trim($producto['nombre']);
            $cantidad = intval($producto['cantidad']);
            $precio_unitario = floatval($producto['precio_unitario']);
            
            // Subir imagen del producto si existe
            $imagen_producto = null;
            if (isset($_FILES['productos']['tmp_name'][$producto['index']]['imagen']) && 
                $_FILES['productos']['tmp_name'][$producto['index']]['imagen']) {
                $imagen_producto = uploadFile([
                    'name' => $_FILES['productos']['name'][$producto['index']]['imagen'],
                    'type' => $_FILES['productos']['type'][$producto['index']]['imagen'],
                    'tmp_name' => $_FILES['productos']['tmp_name'][$producto['index']]['imagen'],
                    'error' => $_FILES['productos']['error'][$producto['index']]['imagen'],
                    'size' => $_FILES['productos']['size'][$producto['index']]['imagen']
                ], 'productos');
            }
            
            $enlace_compra = trim($producto['enlace_compra'] ?? '');
            
            $sql_detalle = "INSERT INTO detalle_compras (compra_id, nombre_producto, imagen_producto, enlace_compra, cantidad, precio_unitario) 
                           VALUES (:compra_id, :nombre_producto, :imagen_producto, :enlace_compra, :cantidad, :precio_unitario)";
            $stmt = $pdo->prepare($sql_detalle);
            $stmt->bindParam(':compra_id', $compra_id);
            $stmt->bindParam(':nombre_producto', $nombre_producto);
            $stmt->bindParam(':imagen_producto', $imagen_producto);
            $stmt->bindParam(':enlace_compra', $enlace_compra);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':precio_unitario', $precio_unitario);
            $stmt->execute();
        }
        
        $pdo->commit();
        
        $_SESSION['success_message'] = "Compra registrada correctamente";
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
        <h1>Nueva Compra</h1>
        <a href="index.php" class="btn btn-secondary">Volver a Compras</a>
    </div>

    <div class="form-container">
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="compraForm">
            <div class="form-group">
                <label for="proveedor_id">Proveedor *</label>
                <select id="proveedor_id" name="proveedor_id" required>
                    <option value="">Seleccione un proveedor</option>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?php echo $proveedor['id']; ?>" <?php echo (isset($_POST['proveedor_id']) && $_POST['proveedor_id'] == $proveedor['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($proveedor['nombre_proveedor']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Productos -->
            <div class="form-group">
                <label>Productos de la Compra *</label>
                <div class="product-items" id="productItems">
                    <!-- Los productos se agregarán dinámicamente aquí -->
                </div>
                <button type="button" class="btn btn-secondary" id="addProduct">+ Agregar Producto</button>
            </div>
            
            <div class="form-group">
                <label for="precio_final">Precio Final (MXN) *</label>
                <input type="number" id="precio_final" name="precio_final" step="0.01" min="0" required readonly
                       value="<?php echo isset($_POST['precio_final']) ? $_POST['precio_final'] : '0'; ?>">
            </div>
            
            <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea id="observaciones" name="observaciones" rows="3"><?php echo isset($_POST['observaciones']) ? htmlspecialchars($_POST['observaciones']) : ''; ?></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Registrar Compra</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productItems = document.getElementById('productItems');
    const addProductBtn = document.getElementById('addProduct');
    const precioFinalInput = document.getElementById('precio_final');
    
    let productIndex = 0;
    
    // Agregar primer producto
    addProductItem();
    
    addProductBtn.addEventListener('click', function() {
        addProductItem();
    });
    
    function addProductItem() {
        const productItem = createProductItem(productIndex);
        productItems.appendChild(productItem);
        productIndex++;
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
                    <label>Nombre del Producto *</label>
                    <input type="text" name="productos[${index}][nombre]" required onchange="calcularTotal()">
                    <input type="hidden" name="productos[${index}][index]" value="${index}">
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
                    <label>Enlace de Compra (URL)</label>
                    <input type="url" name="productos[${index}][enlace_compra]" placeholder="https://...">
                </div>
                <div class="form-group">
                    <label>Imagen del Producto</label>
                    <div class="file-drop">
                        <input type="file" name="productos[${index}][imagen]" accept="image/*" onchange="previewProductImage(this, ${index})">
                        <label for="productos[${index}][imagen]">Arrastre imagen o haga clic</label>
                        <div class="file-preview" id="productImagePreview${index}"></div>
                    </div>
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
            calcularTotal();
        }
    });
});

// Funciones globales para cálculos
function calcularTotal() {
    let total = 0;
    
    document.querySelectorAll('.product-item').forEach(item => {
        const cantidad = parseFloat(item.querySelector('input[name$="[cantidad]"]').value) || 0;
        const precio = parseFloat(item.querySelector('input[name$="[precio_unitario]"]').value) || 0;
        const subtotal = cantidad * precio;
        
        // Actualizar subtotal
        const subtotalInput = item.querySelector('.subtotal');
        subtotalInput.value = '$' + subtotal.toFixed(2);
        
        total += subtotal;
    });
    
    document.getElementById('precio_final').value = total.toFixed(2);
}

function previewProductImage(input, index) {
    const preview = document.getElementById('productImagePreview' + index);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 100px; max-height: 100px;">`;
        };
        
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}
</script>

<?php include '../../includes/footer.php'; ?>