(function($) {
    'use strict';

    // Desabilita teclas de atalho comuns
    $(document).on('keydown', function(e) {
        if (
            // Ctrl+P (Impressão)
            (e.ctrlKey && e.keyCode == 80) ||
            // Ctrl+Shift+I (Dev Tools)
            (e.ctrlKey && e.shiftKey && e.keyCode == 73) ||
            // Ctrl+Shift+C (Dev Tools)
            (e.ctrlKey && e.shiftKey && e.keyCode == 67) ||
            // F12
            (e.keyCode == 123)
        ) {
            e.preventDefault();
            return false;
        }
    });

    // Desabilita clique direito
    $(document).on('contextmenu', '.secure-dacast-protected-content', function(e) {
        e.preventDefault();
        return false;
    });

    // Inicializa heartbeat
    function initHeartbeat() {
        var sessionId = Math.random().toString(36).substring(2);
        
        setInterval(function() {
            $.ajax({
                url: secureDacastHeartbeat.ajax_url,
                type: 'POST',
                data: {
                    action: 'secure_dacast_heartbeat',
                    nonce: secureDacastHeartbeat.nonce,
                    session_id: sessionId
                },
                success: function(response) {
                    if (!response.success) {
                        window.location.reload();
                    }
                },
                error: function() {
                    window.location.reload();
                }
            });
        }, secureDacastHeartbeat.interval);
    }

    // Inicializa quando documento estiver pronto
    $(document).ready(function() {
        if ($('.secure-dacast-protected-content').length) {
            initHeartbeat();
        }
    });

})(jQuery);