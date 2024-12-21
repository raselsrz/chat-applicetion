<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Application</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .typing-text {
            display: none;
        }
        /* slowTextTypeshow */
        .slowTextTypeshow {
            animation: typing 2s steps(40, end);
        }
        @keyframes typing {
            from {
                width: 0;
            }
            to {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <header class="chat-header">
            <h2>Chat Room</h2>
        </header>
        <div class="chat-messages">
            <!-- <div class="message received">
                <p class="message-text">Hi, how are you?</p>
                <span class="time">10:30 AM</span>
            </div>
            <div class="message sent">
                <p class="message-text">I'm good, thanks! You?</p>
                <span class="time">10:31 AM</span>
            </div> -->
            
        </div>
        <!-- tyiping area -->
        <div class="typing-area">
            <p class="typing-text">Typing...</p>
        </div>
        <form class="chat-form">
            <input type="text" placeholder="Type a message..." class="chat-input">
            <button type="submit" class="chat-send">Send</button>
        </form>
    </div>
    <script>
        var name;
        // check name from local storage
        if (localStorage.getItem('name')) {
            name = localStorage.getItem('name');
            document.querySelector('.chat-header h2').textContent = `Chat Room - ${localStorage.getItem('name')}`;
        }else{
            name = prompt('Enter your name: ');
            localStorage.setItem('name', name);
        }
        
        //websocket
        const socket = new WebSocket('ws://127.0.0.1:8980/?name=' + name);

        // initially take name from alert
        document.querySelector('.chat-header h2').textContent = `Chat Room - ${name}`;
        if (name != '') {
        //http://chat.test/getMsg.php?name=Sohag
        var offset = 0;
        fetch(`getMsg.php?name=${name}`)
            .then(response => response.json())
            .then(data => {

                data.forEach(msg => {
                    appendMessage(msg.name === name ? 'sent' : 'received', msg.message);
                    offset = msg.id;
                }); 
            });
        }

        const chatForm = document.querySelector('.chat-form');
        const chatInput = document.querySelector('.chat-input');
        const chatMessages = document.querySelector('.chat-messages');

        chatForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const message = chatInput.value;
            appendMessage('sent', message);
            // send.php
            // fetch('send.php', {
            //     method: 'POST',
            //     body: JSON.stringify({name, message})
            // });

            socket.send(
                JSON.stringify({
                    type: 'message',
                    message: message
                })
            );
            //scroll down
            chatMessages.scrollTop = chatMessages.scrollHeight;


            chatInput.value = '';
        });

        // on typing
        chatInput.addEventListener('input', (e) => {
            let msg = e.target.value;
            socket.send(JSON.stringify({
                type: 'typing',
                user: name,
                message: msg
            }));
        });


        function appendMessage(type, message) {
            const msgDiv = document.createElement('div');
            msgDiv.classList.add('message', type);
            msgDiv.innerHTML = `
                <p class="message-text">${message}</p>
                <span class="time">${new Date().toLocaleTimeString('en-US', {hour: 'numeric', minute: 'numeric', hour12: true})}</span>
            `;
            chatMessages.appendChild(msgDiv);
        }
        //

        // On connection open
        socket.onopen = () => {
            console.log('Connected to WebSocket server');
            const messages = document.getElementById('messages');
            messages.value += "Connected to the server.\n";
        };

        // On receiving a message
        socket.onmessage = (event) => {
            console.log('Message from server:', event.data);
            let data = JSON.parse(event.data);
            console.log(data);
            // if type 
            if (data.type === 'message') {
                appendMessage('received', data.message);
                //scroll down
                chatMessages.scrollTop = chatMessages.scrollHeight;

            } 
            // if typing
            else if (data.type === 'typing') {
                let whoTypin = data.user;
                let msg = data.message;
                document.querySelector('.typing-text').style.display = 'block';
                document.querySelector('.typing-text').innerHTML = `${whoTypin} is typing...` + 
                '<br/> <span style="font-size: 18px;" class="slowTextTypeshow">' + msg + '</span>';
                setTimeout(() => {
                    document.querySelector('.typing-text').style.display = 'none';
                    
                }, 4000);
            }
        };

        // On connection close
        socket.onclose = () => {
            console.log('WebSocket connection closed');
            const messages = document.getElementById('messages');
            messages.value += "Connection closed.\n";
        };

        // On error
        socket.onerror = (error) => {
            console.error('WebSocket error:', error);
            const messages = document.getElementById('messages');
            messages.value += "An error occurred.\n";
        };
         

    </script>
</body>
</html>
