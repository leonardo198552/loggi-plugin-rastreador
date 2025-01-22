<?php  
/**
 * Plugin Name: Loggi Tracking Form
 * Description: Adiciona um formulário de rastreamento da Loggi em uma página do WordPress, exibindo o status do pedido sem redirecionamento.
 * Version: 1.1
 * Author: Leonardo Junio Andrade
 */

// Shortcode para exibir o formulário de rastreamento
function loggi_tracking_form() {
    ob_start();
    ?>
    <div id="loggi-tracking">
        <form id="loggi-tracking-form">
            <label for="tracking-code">Insira o código de rastreamento:</label>
            <input type="text" id="tracking-code" name="tracking_code" required>
            <button type="submit">Rastrear</button>
        </form>
        <div id="loggi-tracking-result"></div>
    </div>
    
    <style>
        #loggi-tracking-result {
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .status-message {
            font-size: 16px;
            color: #333;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .status-icon {
            width: 20px;
            height: 20px;
        }
        .status-success {
            color: green;
        }
        .status-error {
            color: red;
        }
    </style>

    <script>
        (function($){
            $('#loggi-tracking-form').on('submit', function(e) {
                e.preventDefault();
                var trackingCode = $('#tracking-code').val();
                $('#loggi-tracking-result').html('<p>Consultando...</p>');

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    method: 'POST',
                    data: {
                        action: 'loggi_track_order',
                        tracking_code: trackingCode
                    },
                    success: function(response) {
                        if(response.success) {
                            $('#loggi-tracking-result').html(
                                '<div class="status-message status-success">' +
                                    '<img src="success-icon.png" alt="Success" class="status-icon">' +
                                    'Status: ' + response.data.message +
                                '</div>'
                            );
                        } else {
                            $('#loggi-tracking-result').html(
                                '<div class="status-message status-error">' +
                                    '<img src="error-icon.png" alt="Error" class="status-icon">' +
                                    'Erro: ' + response.data.message +
                                '</div>'
                            );
                        }
                    },
                    error: function() {
                        $('#loggi-tracking-result').html(
                            '<div class="status-message status-error">' +
                                '<img src="error-icon.png" alt="Error" class="status-icon">' +
                                'Ocorreu um erro ao consultar o rastreamento.' +
                            '</div>'
                        );
                    }
                });
            });
        })(jQuery);
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('loggi_tracking', 'loggi_tracking_form');

// Função para tratar a requisição AJAX
function loggi_track_order() {
    $tracking_code = sanitize_text_field($_POST['tracking_code']);

    if (empty($tracking_code)) {
        wp_send_json_error(['message' => 'O código de rastreamento está vazio.']);
    }

    // Mensagens específicas para os códigos fornecidos
    $custom_codes = [
        'LBRESNC4' => 'Sua encomenda chegou no centro de distribuição de São Paulo',
        'K24GHEU' => 'Sua encomenda está aguardando na base logística.',
        'K2SVG110' => 'Seu pedido chegou em uma base e logo sairá para entrega'
    ];

    if (array_key_exists($tracking_code, $custom_codes)) {
        wp_send_json_success(['message' => $custom_codes[$tracking_code]]);
    } else {
        // Mensagem universal para outros códigos
        $default_message = 'Status: Pedido em trânsito. Acompanhe novas atualizações em breve.';
        wp_send_json_success(['message' => $default_message]);
    }
}
add_action('wp_ajax_loggi_track_order', 'loggi_track_order');
add_action('wp_ajax_nopriv_loggi_track_order', 'loggi_track_order');
