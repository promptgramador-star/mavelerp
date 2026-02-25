-- Datos Iniciales para ERP Propietario RD

-- Roles Base
INSERT INTO roles (name, description) VALUES 
('SUPER_ADMIN', 'Acceso total al sistema, módulos y usuarios.'),
('ADMIN', 'Administrador de la empresa con acceso a configuración y reportes.'),
('USER', 'Usuario operativo con acceso limitado a módulos asignados.');

-- Módulos Base
INSERT INTO modules (name, is_premium) VALUES 
('Facturacion', 0),
('Contabilidad', 1),
('Inventario', 0),
('CRM', 0);

-- Secuencias Iniciales para 2026
INSERT INTO document_sequences (document_type, prefix, year, current_number, reset_type) VALUES 
('COT', 'COT', '26', 0, 'YEARLY'),
('FAC', 'FAC', '26', 0, 'YEARLY'),
('COND', 'COND', '26', 0, 'YEARLY');

-- Plan de Cuentas Básico (Ejemplo RTD)
INSERT INTO chart_of_accounts (code, name, type) VALUES 
('1101', 'Caja y Bancos', 'ASSET'),
('1201', 'Cuentas por Cobrar', 'ASSET'),
('2101', 'Cuentas por Pagar', 'LIABILITY'),
('3101', 'Capital Social', 'EQUITY'),
('4101', 'Ventas de Productos', 'INCOME'),
('5101', 'Gastos Generales', 'EXPENSE');

-- Activación de Módulos Base
INSERT INTO module_license (module_id, is_enabled, activated_at) VALUES 
(1, 1, NOW()),
(2, 0, NULL),
(3, 1, NOW()),
(4, 1, NOW());
