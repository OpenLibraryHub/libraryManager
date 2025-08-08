<?php
require_once 'config/autoload.php';

use App\Models\Librarian;

try {
    $librarianModel = new Librarian();
    
    // Update password to "12345"
    $newPassword = "12345";
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    
    // Update the password
    $result = $librarianModel->updatePassword(1, $newPassword);
    
    if ($result) {
        echo "✅ Password updated successfully!\n";
        echo "New password: 12345\n";
    } else {
        echo "❌ Failed to update password\n";
    }
    
    // Test the reset token storage again
    $librarian = $librarianModel->findByEmail('gimena@gmail.com');
    
    if ($librarian) {
        echo "✅ Found librarian: " . $librarian['email'] . "\n";
        
        // Test storing reset token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        echo "Generated token: " . $token . "\n";
        
        $result = $librarianModel->storeResetToken($librarian['id'], $token, $expires);
        
        if ($result) {
            echo "✅ Reset token stored successfully!\n";
            echo "Reset link: http://localhost/library/reset-password.php?token=" . $token . "\n";
        } else {
            echo "❌ Failed to store reset token\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
