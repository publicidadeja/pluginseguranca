<?php
if (!defined('WPINC')) {
    die;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Acesso Restrito</title>
    <?php wp_head(); ?>
    <style>
        .secure-dacast-verification {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .secure-dacast-form-container {
            background: white;
            padding: 30px;
            border-radius: 5px;
            max-width: 400px;
            width: 100%;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
        }
        #verification-message {
            margin-top: 15px;
            padding: 10px;
            display: none;
        }
        .error { color: red; }
        .success { color: green; }
    </style>
</head>
<body>
    <div class="secure-dacast-verification">
        <div class="secure-dacast-form-container">
            <h2>Acesso Restrito</h2>
            <div class="form-group">
                <label for="cpf">CPF:</label>
                <input type="text" id="cpf" name="cpf" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <button type="button" id="verify-access">Verificar Acesso</button>
            </div>
            <div id="verification-message"></div>
        </div>
    </div>

    <?php
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-mask');
    wp_print_scripts('jquery');
    wp_print_scripts('jquery-mask');
    ?>

    <script type="text/javascript">
    jQuery(document).ready(function($) {
        $('#cpf').mask('000.000.000-00');

        $('#verify-access').on('click', function() {
            var $message = $('#verification-message');
            var cpf = $('#cpf').val();
            var email = $('#email').val();

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'verify_dacast_access',
                    cpf: cpf,
                    email: email,
                    nonce: '<?php echo wp_create_nonce('secure_dacast_verify'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $message.removeClass('error').addClass('success')
                            .text('Acesso autorizado! Redirecionando...')
                            .show();
                        setTimeout(function() {
                            window.location.reload();
                        }, 1500);
                    } else {
                        $message.removeClass('success').addClass('error')
                            .text(response.data)
                            .show();
                    }
                },
                error: function() {
                    $message.removeClass('success').addClass('error')
                        .text('Erro ao verificar acesso. Tente novamente.')
                        .show();
                }
            });
        });
    });
    </script>
    <?php wp_footer(); ?>
</body>
</html>