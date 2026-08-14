USE stockflow;

INSERT IGNORE INTO products (sku, name, description, price, quantity, status) VALUES
('KB-100', 'Mechanical Keyboard', 'Hot-swappable keyboard for development workstations.', 89.99, 12, 'active'),
('MS-210', 'Wireless Mouse', 'Ergonomic wireless mouse with adjustable DPI.', 34.50, 28, 'active'),
('DK-320', 'USB-C Dock', 'USB-C docking station with display and network ports.', 74.00, 7, 'active'),
('HD-440', 'External SSD 1TB', 'Portable solid-state drive for backups and project storage.', 109.95, 5, 'active'),
('CM-550', 'Webcam 1080p', 'USB webcam retained as an inactive inventory item.', 29.00, 0, 'inactive');
