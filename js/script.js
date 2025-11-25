// Funcionalidades JavaScript para RUFIXNET

document.addEventListener('DOMContentLoaded', function() {
    // Menu toggle para móviles
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Cerrar menú al hacer clic fuera en móviles
    document.addEventListener('click', function(event) {
        if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('active')) {
            if (!sidebar.contains(event.target) && event.target !== menuToggle) {
                sidebar.classList.remove('active');
            }
        }
    });
    
    // Funcionalidad de drag and drop para archivos
    const fileDrops = document.querySelectorAll('.file-drop');
    
    fileDrops.forEach(dropZone => {
        const fileInput = dropZone.querySelector('input[type="file"]');
        const fileLabel = dropZone.querySelector('label');
        
        // Click en el área
        dropZone.addEventListener('click', function(e) {
            if (e.target !== fileInput) {
                fileInput.click();
            }
        });
        
        // Drag over
        dropZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        
        // Drag leave
        dropZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
        });
        
        // Drop
        dropZone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            
            if (e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                updateFileLabel(fileLabel, e.dataTransfer.files[0]);
            }
        });
        
        // Cambio mediante input
        fileInput.addEventListener('change', function() {
            if (fileInput.files.length) {
                updateFileLabel(fileLabel, fileInput.files[0]);
            }
        });
        
        function updateFileLabel(label, file) {
            label.textContent = `Archivo seleccionado: ${file.name}`;
        }
    });
    
    // Funcionalidad para agregar productos dinámicamente
    const addProductBtn = document.getElementById('addProduct');
    const productItems = document.getElementById('productItems');
    
    if (addProductBtn && productItems) {
        addProductBtn.addEventListener('click', function() {
            const productCount = productItems.querySelectorAll('.product-item').length;
            const newProduct = createProductItem(productCount);
            productItems.appendChild(newProduct);
        });
    }
    
    // Eliminar productos
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-product')) {
            e.target.closest('.product-item').remove();
            updateProductIndexes();
        }
    });
    
    function createProductItem(index) {
        const div = document.createElement('div');
        div.className = 'product-item';
        div.innerHTML = `
            <div class="product-item-header">
                <h4>Producto ${index + 1}</h4>
                <button type="button" class="remove-product">Eliminar</button>
            </div>
            <div class="product-item-fields">
                <div class="form-group">
                    <label>Nombre del Producto</label>
                    <input type="text" name="productos[${index}][nombre]" required>
                </div>
                <div class="form-group">
                    <label>Cantidad</label>
                    <input type="number" name="productos[${index}][cantidad]" min="1" required>
                </div>
                <div class="form-group">
                    <label>Precio Unitario</label>
                    <input type="number" name="productos[${index}][precio_unitario]" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label>Imagen (opcional)</label>
                    <input type="file" name="productos[${index}][imagen]" accept="image/*">
                </div>
            </div>
        `;
        return div;
    }
    
    function updateProductIndexes() {
        const items = productItems.querySelectorAll('.product-item');
        items.forEach((item, index) => {
            const header = item.querySelector('h4');
            header.textContent = `Producto ${index + 1}`;
            
            // Actualizar los names de los inputs
            const inputs = item.querySelectorAll('input');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name) {
                    const newName = name.replace(/productos\[\d+\]/, `productos[${index}]`);
                    input.setAttribute('name', newName);
                }
            });
        });
    }
    
    // Cálculo automático de totales
    const calculateTotals = function() {
        const productItems = document.querySelectorAll('.product-item');
        let total = 0;
        
        productItems.forEach(item => {
            const cantidad = parseFloat(item.querySelector('input[name$="[cantidad]"]').value) || 0;
            const precio = parseFloat(item.querySelector('input[name$="[precio_unitario]"]').value) || 0;
            total += cantidad * precio;
        });
        
        const gastosExtra = parseFloat(document.getElementById('gastos_extra').value) || 0;
        total += gastosExtra;
        
        document.getElementById('precio_final').value = total.toFixed(2);
    };
    
    // Event listeners para cálculo de totales
    document.addEventListener('input', function(e) {
        if (e.target.name && (
            e.target.name.includes('[cantidad]') || 
            e.target.name.includes('[precio_unitario]') ||
            e.target.id === 'gastos_extra'
        )) {
            calculateTotals();
        }
    });
    
    // Confirmación para eliminar
    const deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que quieres eliminar este registro? Esta acción no se puede deshacer.')) {
                e.preventDefault();
            }
        });
    });
    
    // Filtros de fecha
    const dateFilters = document.querySelectorAll('.date-filter');
    dateFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });
});

// Función para mostrar preview de imagen
function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px;">`;
        };
        
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
}