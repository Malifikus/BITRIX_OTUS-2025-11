console.log('[DZ8] JS loaded');

// Пропустить клик без показа попапа
window.__dz8SkipPopup = false;

BX.ready(function() {
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('button, .ui-btn');
        if (!btn) return;
        
        var text = btn.textContent.trim().toLowerCase();
        if (text.includes('начать') || text.includes('возобновить')) {
            var popup = btn.closest('[class*="popup"], [class*="timeman"]');
            if (!popup) return;
            
            console.log('[DZ8] Start button clicked, skip=', window.__dz8SkipPopup);
            
            // Даём сработать оригиналу
            if (window.__dz8SkipPopup) {
                window.__dz8SkipPopup = false;
                console.log('[DZ8] Allowing original action');
                return;
            }
            
            // Показываем попап
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            
            console.log('[DZ8] Showing confirm popup');
            
            showConfirmPopup(function(confirmed) {
                if (confirmed) {
                    console.log('[DZ8] Confirmed - triggering original click');
                    // Устанавливаем флаг
                    window.__dz8SkipPopup = true;
                    // Триггерим клик
                    btn.click();
                } else {
                    console.log('[DZ8] Cancelled');
                }
            });
        }
    }, true);
});

function showConfirmPopup(callback) {
    var popup = BX.PopupWindowManager.create('dz8-start-confirm', null, {
        content: '<div style="padding:20px;">' +
            '<p>Вы хотите начать рабочий день?</p>' +
            '<button id="dz8-yes" class="ui-btn ui-btn-primary" style="margin-right:10px;">Да</button>' +
            '<button id="dz8-no" class="ui-btn ui-btn-link">Отмена</button>' +
            '</div>',
        titleBar: 'Подтверждение',
        closeIcon: true,
        overlay: {backgroundColor: 'black', opacity: '50'},
        zIndex: 2100,
        autoHide: false,
        buttons: []
    });
    
    popup.show();
    
    setTimeout(function() {
        var yes = document.getElementById('dz8-yes');
        var no = document.getElementById('dz8-no');
        
        if (yes) {
            yes.onclick = function() {
                popup.close();
                callback(true);
            };
        }
        if (no) {
            no.onclick = function() {
                popup.close();
                callback(false);
            };
        }
    }, 50);
}
