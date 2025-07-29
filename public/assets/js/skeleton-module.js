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
        // const authUserID = window.openemr?.userData?.authUserID || '';
        const userUuid = window.openemr?.userData?.uuid || '';
        if (!userUuid) {
            console.error('No user UUID available');
            alert('User UUID is missing.');
            return;
        }
        let wpeckerUrl = `https://copilot.wpecker.ai?user_id=${encodeURIComponent(userUuid)}`;
        const userData = window.openemr.userData;
        const queryParams = new URLSearchParams({
            uid: userUuid,
            username: userData.username || '',
            first_name: userData.first_name || '',
            last_name: userData.last_name || '',
            email: userData.email || ''
        }).toString();
        // Update URL with additional parameters
        wpeckerUrl = `https://copilot.wpecker.ai?${queryParams}`;
        button.addEventListener('click', function() {
            window.open(wpeckerUrl, '_blank');
        });
        document.body.appendChild(button);
    }

    window.addEventListener('load', init);
})(window || {});