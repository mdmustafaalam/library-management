-- =====================================================
-- Seed sample data (run once, on a fresh/development DB)
-- Assumes: member id=1 (MEM001) already exists.
-- Generates book/member codes matching the app's logic.
-- =====================================================

-- ---------- Books ----------
INSERT INTO books (id, book_code, title, author, category, isbn, publisher, total_copies, available_copies, status) VALUES
(1,  'BOOK001', 'The Alchemist',                 'Paulo Coelho',          'Fiction',     '978-COELHO-001',   'HarperOne',          5, 4, 'Available'),
(2,  'BOOK002', 'Atomic Habits',                 'James Clear',           'Self Help',   '978-CLEAR-002',    'Avery',              6, 5, 'Available'),
(3,  'BOOK003', 'Clean Code',                    'Robert C. Martin',      'Programming', '978-MARTIN-003',   'Prentice Hall',      4, 4, 'Available'),
(4,  'BOOK004', 'The Pragmatic Programmer',      'Andrew Hunt',           'Programming', '978-HUNT-004',     'Addison-Wesley',     3, 3, 'Available'),
(5,  'BOOK005', 'Rich Dad Poor Dad',             'Robert Kiyosaki',       'Finance',     '978-KIYO-005',     'Plata Publishing',   5, 4, 'Available'),
(6,  'BOOK006', 'Sapiens',                       'Yuval Noah Harari',     'History',     '978-HARARI-006',   'Vintage',            4, 3, 'Available'),
(7,  'BOOK007', '1984',                          'George Orwell',         'Fiction',     '978-ORWELL-007',   'Secker & Warburg',   7, 7, 'Available'),
(8,  'BOOK008', 'Deep Work',                     'Cal Newport',           'Self Help',   '978-NEWPORT-008',  'Grand Central',      4, 4, 'Available'),
(9,  'BOOK009', 'Introduction to Algorithms',    'Thomas H. Cormen',      'Programming', '978-CORMEN-009',   'MIT Press',          2, 1, 'Available'),
(10, 'BOOK010', 'The Psychology of Money',       'Morgan Housel',         'Finance',     '978-HOUSEL-010',   'Harriman House',     5, 4, 'Available');

-- ---------- Members ----------
INSERT INTO members (id, member_code, name, email, phone, address, status) VALUES
(2, 'MEM002', 'Aarav Sharma',   'aarav.sharma@example.com',   '9876543210', 'Mumbai, Maharashtra',            'Active'),
(3, 'MEM003', 'Priya Patel',    'priya.patel@example.com',    '9876543211', 'Pune, Maharashtra',              'Active'),
(4, 'MEM004', 'Rohan Gupta',    'rohan.gupta@example.com',    '9876543212', 'Delhi',                          'Active'),
(5, 'MEM005', 'Sneha Iyer',     'sneha.iyer@example.com',     '9876543213', 'Bengaluru, Karnataka',           'Active'),
(6, 'MEM006', 'Vikram Singh',   'vikram.singh@example.com',   '9876543214', 'Jaipur, Rajasthan',              'Active'),
(7, 'MEM007', 'Ananya Das',     'ananya.das@example.com',     '9876543215', 'Kolkata, West Bengal',           'Active'),
(8, 'MEM008', 'Kabir Mehta',    'kabir.mehta@example.com',    '9876543216', 'Ahmedabad, Gujarat',             'Active'),
(9, 'MEM009', 'Ishita Rao',     'ishita.rao@example.com',     '9876543217', 'Chennai, Tamil Nadu',            'Inactive');

-- ---------- Transactions ----------
INSERT INTO transactions (id, member_id, book_id, issue_date, due_date, return_date, status) VALUES
(1,  1, 1,  '2026-08-10', '2026-08-24', NULL,          'Overdue'),
(2,  2, 2,  '2026-08-28', '2026-09-11', NULL,          'Issued'),
(3,  3, 3,  '2026-08-01', '2026-08-15', '2026-08-18',  'Returned'),
(4,  4, 4,  '2026-07-20', '2026-08-03', '2026-08-10',  'Returned'),
(5,  5, 5,  '2026-08-15', '2026-08-29', NULL,          'Overdue'),
(6,  6, 6,  '2026-09-01', '2026-09-15', NULL,          'Issued'),
(7,  2, 7,  '2026-07-10', '2026-07-24', '2026-07-20',  'Returned'),
(8,  3, 8,  '2026-08-05', '2026-08-19', '2026-08-25',  'Returned'),
(9,  7, 9,  '2026-08-30', '2026-09-13', NULL,          'Issued'),
(10, 8, 10, '2026-09-05', '2026-09-19', NULL,          'Issued');

-- ---------- Fines (₹10 per overdue day) ----------
INSERT INTO fines (transaction_id, member_id, amount, paid_amount, status, payment_date) VALUES
(3, 3,  30.00, 30.00, 'Paid',    '2026-08-18 12:30:00'),
(4, 4,  70.00, 40.00, 'Partial', NULL),
(8, 3,  60.00,  0.00, 'Pending', NULL);

-- ---------- Reservations ----------
INSERT INTO reservations (member_id, book_id, reservation_date, status) VALUES
(1, 10, '2026-09-05', 'Pending'),
(2, 9,  '2026-09-01', 'Approved'),
(3, 7,  '2026-08-28', 'Rejected'),
(4, 5,  '2026-08-15', 'Completed'),
(6, 2,  '2026-09-06', 'Pending');