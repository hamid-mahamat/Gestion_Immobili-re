<?php
session_start();
$conn = mysqli_connect("127.0.0.1", "root", "", "gestion_immo");
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['connexion'])) {
    $nom_user = mysqli_real_escape_string($conn, $_POST['nom_user']);
    $mot_passe = mysqli_real_escape_string($conn, $_POST['mot_passe']);
    
    $result = mysqli_query($conn, "SELECT id_user, nom_user FROM user WHERE nom_user='$nom_user' AND mot_passe='$mot_passe'");
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nom_user'] = $user['nom_user'];
        header("Location: index.php");
        exit();
    } else {
        $error_message = 'Identifiants incorrects !';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Gestion Immobilière</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4cc9f0;
            --primary-dark: #3a56d4;
            --secondary: #7209b7;
            --light: #f8f9fa;
            --dark: #212529;
            --gray: #6c757d;
            --light-gray: #e9ecef;
            --border-radius: 12px;
            --box-shadow: 0 8px 20px rgba(0,0,0,0.08);
            --transition: all 0.3s ease;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            position: relative;
            z-index: 1;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 30px 30px 60px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-header::before {
            content: "";
            position: absolute;
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%;
        }
        
        .logo {
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }
        
        .logo img {
            height: 70px;
            filter: drop-shadow(0 3px 10px rgba(0,0,0,0.15));
        }
        
        .login-header h1 {
            font-size: 1.8rem;
            margin-bottom: 8px;
            font-weight: 700;
            position: relative;
            z-index: 2;
        }
        
        .login-header p {
            font-size: 1rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }
        
        .login-form {
            padding: 30px;
            position: relative;
            margin-top: -40px;
            z-index: 3;
        }
        
        .form-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            padding: 25px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 25px;
        }
        
        .form-header h2 {
            font-size: 1.6rem;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .form-header p {
            color: var(--gray);
            font-size: 0.95rem;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--dark);
            font-size: 0.95rem;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-with-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
            font-size: 1.1rem;
        }
        
        .form-control {
            width: 100%;
            padding: 14px 15px 14px 45px;
            border: 2px solid var(--light-gray);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }
        
        .password-container {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 1.1rem;
        }
        
        .btn {
            display: block;
            width: 100%;
            padding: 14px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }
        
        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 12px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-size: 0.95rem;
            text-align: center;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .additional-links {
            text-align: center;
            margin-top: 20px;
            font-size: 0.9rem;
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
        .additional-links a {
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
        }
        
        .additional-links a:hover {
            color: var(--secondary);
            text-decoration: underline;
        }
        
        .footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 15px;
            border-top: 1px solid var(--light-gray);
            font-size: 0.85rem;
            color: var(--gray);
        }
        
        @media (max-width: 480px) {
            .login-container {
                max-width: 95%;
            }
            
            .login-header {
                padding: 25px 20px 50px;
            }
            
            .login-form {
                padding: 20px;
            }
            
            .form-card {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            
            <h1>Gestion Immo</h1>
            <p>Votre solution de gestion immobilière</p>
        </div>
        
        <div class="login-form">
            <div class="form-card">
                <div class="form-header">
                    <h2>Connexion à votre compte</h2>
                    <p>Saisissez vos identifiants pour continuer</p>
                </div>
                
                <?php if(!empty($error_message)): ?>
                    <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <form method="post" action="connexion.php">
                    <div class="form-group">
                        <label for="nom_user">Nom d'utilisateur</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" required name="nom_user" id="nom_user" class="form-control" placeholder="Entrez votre nom d'utilisateur">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="mot_passe">Mot de passe</label>
                        <div class="input-with-icon">
                            <i class="fas fa-lock"></i>
                            <div class="password-container">
                                <input type="password" required name="mot_passe" id="mot_passe" class="form-control" placeholder="Entrez votre mot de passe">
                                <button type="button" class="toggle-password" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" name="connexion" class="btn">Se connecter</button>
                    
                    <div class="additional-links">
                        <a href="#"><i class="fas fa-key"></i> Mot de passe oublié</a>
                        <a href="#"><i class="fas fa-headset"></i> Support</a>
                    </div>
                </form>
            </div>
            
            <div class="footer">
                © 2025 Gestion Immo - Tous droits réservés
            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('mot_passe');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            const icon = this.querySelector('i');
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
        
        document.addEventListener('DOMContentLoaded', function() {
            const loginContainer = document.querySelector('.login-container');
            loginContainer.style.opacity = '0';
            loginContainer.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                loginContainer.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                loginContainer.style.opacity = '1';
                loginContainer.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>