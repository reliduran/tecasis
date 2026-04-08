// assets/js/bot.js - L¨®gica Corregida

function toggleChat() {
    const widget = document.getElementById('chatbot-widget');
    const body = document.getElementById('chat-body');
    const icon = document.getElementById('chat-icon');

    // Si el widget est¨¢ totalmente oculto (display: none), lo mostramos primero
    if (widget.style.display === 'none') {
        widget.style.display = 'block';
        // Y aseguramos que el cuerpo est¨¦ visible
        body.style.display = 'flex';
        icon.className = 'fa-solid fa-chevron-down';
        return; 
    }

    // Si ya est¨¢ visible, alternamos entre minimizar y maximizar
    if (body.style.display === 'none' || body.style.display === '') {
        body.style.display = 'flex';
        icon.className = 'fa-solid fa-chevron-down'; // Flecha abajo
    } else {
        body.style.display = 'none';
        icon.className = 'fa-solid fa-chevron-up'; // Flecha arriba
    }
}

function handleEnter(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
}

async function sendMessage() {
    const input = document.getElementById('user-input');
    const message = input.value.trim();
    const chatBox = document.getElementById('chat-messages');

    if (message === "") return;

    // 1. Mostrar mensaje del usuario
    const userDiv = document.createElement('div');
    userDiv.className = 'user-msg';
    userDiv.textContent = message;
    chatBox.appendChild(userDiv);
    
    input.value = ''; // Limpiar input
    chatBox.scrollTop = chatBox.scrollHeight;

    // 2. Mostrar "Escribiendo..."
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'bot-msg loading';
    loadingDiv.textContent = '...';
    loadingDiv.id = 'temp-loading';
    chatBox.appendChild(loadingDiv);

    try {
        // 3. Enviar a PHP
        const response = await fetch('api/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: message })
        });

        const data = await response.json();

        // Quitar loading
        document.getElementById('temp-loading').remove();

        // 4. Mostrar respuesta del Bot
        const botDiv = document.createElement('div');
        botDiv.className = 'bot-msg';
        botDiv.innerHTML = data.reply; 
        chatBox.appendChild(botDiv);

    } catch (error) {
        console.error('Error:', error);
        if(document.getElementById('temp-loading')) document.getElementById('temp-loading').remove();
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'bot-msg error';
        errorDiv.textContent = "Error de conexi¨®n.";
        chatBox.appendChild(errorDiv);
    }

    chatBox.scrollTop = chatBox.scrollHeight;
}