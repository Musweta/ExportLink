<?php
require_once 'header.php';
require_once 'db_conn.php';

// Handle user registration
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize inputs
    $username = filter_input(INPUT_POST, 'username', FILTER_UNSAFE_RAW);
    $username = trim(htmlspecialchars($username ?? ''));
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'] ?? '', PASSWORD_BCRYPT);
    $role = filter_input(INPUT_POST, 'role', FILTER_UNSAFE_RAW);
    $role = trim(htmlspecialchars($role ?? ''));

    // Validate inputs
    if (empty($username) || empty($email) || empty($password) || !in_array($role, ['farmer', 'importer', 'admin'])) {
        echo "<div class='alert alert-danger'>Invalid input data.</div>";
    } elseif ($role == 'importer' && !isset($_FILES['documents'])) {
        echo "<div class='alert alert-danger'>Please upload all required documents.</div>";
    } else {
        try {
            // Start transaction for user and document insertion
            $pdo->beginTransaction();

            // Exempt admins from approval
            $is_approved = $role == 'admin' ? 1 : 0;
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_approved) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $password, $role, $is_approved]);
            $user_id = $pdo->lastInsertId();

            // Handle importer document uploads
            if ($role == 'importer') {
                $required_docs = [
                    'import_license' => 'Import/Export License',
                    'business_registration' => 'Business Registration Certificate',
                    'tax_id' => 'Tax Identification Certificate',
                    'customs_registration' => 'Customs Registration Number',
                    'import_declaration' => 'Import Declaration Form',
                    'bill_of_lading' => 'Bill of Lading/Airway Bill',
                    'past_import_records' => 'Past Import Records',
                    'business_premises' => 'Proof of Physical Business Premises'
                ];
                $upload_dir = '../Uploads/';
                foreach ($required_docs as $doc_key => $doc_name) {
                    if (!isset($_FILES['documents']['name'][$doc_key]) || $_FILES['documents']['error'][$doc_key] != UPLOAD_ERR_OK) {
                        throw new Exception("Missing or invalid $doc_name.");
                    }
                    $file_name = $user_id . '_' . $doc_key . '_' . time() . '_' . basename($_FILES['documents']['name'][$doc_key]);
                    $file_path = $upload_dir . $file_name;
                    if (!move_uploaded_file($_FILES['documents']['tmp_name'][$doc_key], $file_path)) {
                        throw new Exception("Failed to upload $doc_name.");
                    }
                    $stmt = $pdo->prepare("INSERT INTO importer_documents (user_id, document_type, file_path) VALUES (?, ?, ?)");
                    $stmt->execute([$user_id, $doc_key, $file_path]);
                }
            }

            $pdo->commit();
            $message = $role == 'admin' 
                ? "Registration successful! Please <a href='login.php'>login</a>." 
                : "Registration successful! Awaiting admin approval.";
            echo "<div class='alert alert-success'>$message</div>";
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Registration error: " . $e->getMessage());
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>

<!-- Responsive registration form -->
<div class="container mt-5">
    <h2>Register</h2>
    <form method="POST" action="" enctype="multipart/form-data" class="col-md-6 mx-auto">
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" required placeholder="Enter username">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" required placeholder="Enter email">
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
        </div>
        <div class="mb-3">
            <label for="role" class="form-label">Role</label>
            <select class="form-select" id="role" name="role" required onchange="toggleImporterDocs()">
                <option value="" disabled selected>Select role</option>
                <option value="farmer">Farmer</option>
                <option value="importer">Importer</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <!-- Importer document uploads (hidden by default) -->
        <div id="importer-docs" style="display: none;">
            <h4>Importer Documents</h4>
            <div class="mb-3">
                <label for="import_license" class="form-label">Import/Export License</label>
                <input type="file" class="form-control" id="import_license" name="documents[import_license]" accept=".pdf,.jpg,.png">
            </div>
            <div class="mb-3">
                <label for="business_registration" class="form-label">Business Registration Certificate</label>
                <input type="file" class="form-control" id="business_registration" name="documents[business_registration]" accept=".pdf,.jpg,.png">
            </div>
            <div class="mb-3">
                <label for="tax_id" class="form-label">Tax Identification Certificate (TIN/VAT)</label>
                <input type="file" class="form-control" id="tax_id" name="documents[tax_id]" accept=".pdf,.jpg,.png">
            </div>
            <div class="mb-3">
                <label for="customs_registration" class="form-label">Customs Registration Number or EORI</label>
                <input type="file" class="form-control" id="customs_registration" name="documents[customs_registration]" accept=".pdf,.jpg,.png">
            </div>
            <div class="mb-3">
                <label for="import_declaration" class="form-label">Import Declaration Form (IDF)</label>
                <input type="file" class="form-control" id="import_declaration" name="documents[import_declaration]" accept=".pdf,.jpg,.png">
            </div>
            <div class="mb-3">
                <label for="bill_of_lading" class="form-label">Bill of Lading or Airway Bill</label>
                <input type="file" class="form-control" id="bill_of_lading" name="documents[bill_of_lading]" accept=".pdf,.jpg,.png">
            </div>
            <div class="mb-3">
                <label for="past_import_records" class="form-label">Past Import Records</label>
                <input type="file" class="form-control" id="past_import_records" name="documents[past_import_records]" accept=".pdf,.jpg,.png">
            </div>
            <div class="mb-3">
                <label for="business_premises" class="form-label">Proof of Physical Business Premises</label>
                <input type="file" class="form-control" id="business_premises" name="documents[business_premises]" accept=".pdf,.jpg,.png">
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>
</div>

<script>
function toggleImporterDocs() {
    const role = document.getElementById('role').value;
    const importerDocs = document.getElementById('importer-docs');
    importerDocs.style.display = role === 'importer' ? 'block' : 'none';
    // Make importer document fields required only when role is importer
    const inputs = importerDocs.querySelectorAll('input[type="file"]');
    inputs.forEach(input => input.required = role === 'importer');
}
</script>

<?php require_once 'footer.php'; ?>