(function(window) {
    function init() {
                             
        const button = document.createElement('button');
        button.innerText = 'Woodpecker Copilot';
                                      
        Object.assign(button.style, {
            position: 'fixed',
            bottom: '20px',
            right: '20px',
            padding: '10px 20px',
            backgroundColor: '#007bff',
            color: '#fff',
            border: 'none',
            borderRadius: '4px',
            cursor: 'pointer',
            zIndex: 9999,
            boxShadow: '0 2px 6px rgba(0,0,0,0.3)'
        });
        // ............
        button.addEventListener('click', function() {
            window.open('https://devcopilot.wpecker.ai/', '_blank');
        });
        document.body.appendChild(button);
    }

    window.addEventListener('load', init);
})(window || {});