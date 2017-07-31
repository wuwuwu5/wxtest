<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel</title>
    <style>
        .room {
            width: 100%;
            height: 20px;
            margin-bottom: 50px;
            text-align: center;
            line-height: 20px;
            border: 1px black solid;
        }

        .window {
            float: left;
            width: 75%;
            height: 500px;
            border: 1px black solid;
        }

        .users {
            float: right;
            width: 20%;
            height: 500px;
            border: 1px black solid;
        }

        .send {
            margin-top: 30px;
        }

        #message {
            width: 75%;
            height: 50px;
        }

        #send {
            width: 50px;
            height: 50px;
        }
    </style>
</head>
<body>
<div id="app">
    <div class="room">
        房间号：
    </div>

    <div class="content">
        <div class="window">
            <ul>
                <li v-for="message in messages">
                    @{{ message }}
                </li>
            </ul>
        </div>
        <!--// 当前的用户-->
        <div class="users">
            <ul>
                <li v-for="user in users">
                    @{{ user }}
                </li>
            </ul>
        </div>
        <div style="clear: both"></div>
    </div>

    <div class="send" v-show="send">
        <input type="text" v-model="message" id="message">
        <input type="button" value="发送" @click="sendMessage">
    </div>

    <div class="" v-show="register">
        <input type="text" v-model="username" id="registerUser">
        <input type="button" value="注册" @click="registerUser">
    </div>

    <div class="">
        <ul>
            <li v-for="room in roomUsers">@{{ room }}</li>
        </ul>
    </div>
</div>
</body>

<script src="https://cdn.bootcss.com/socket.io/2.0.3/socket.io.slim.js"></script>
<script src="https://unpkg.com/vue"></script>
<script>
    // 链接
    var socket = io.connect('192.168.10.10:3000');

    new Vue({
        el: '#app',
        data: {
            'message': '',
            'username': '',
            'register': true,
            'send': false,
            'users': '',
            'messages': [],
            'roomUsers': []
        },
        created(){
            // 进入房间的用户
            socket.on('room prompt', function (response) {
                this.roomUsers.push(response);
            }.bind(this));

            // 接收新消息
            socket.on('room new message', function (response) {
                this.messages.push(response.user + '说： ' + response.message);
            }.bind(this));

        },
        methods: {
            // 发送消息
            sendMessage(){
                socket.emit('room send message', this.message);
                this.message = '';
            },
            // 注册用户
            registerUser(){
                socket.emit('room new user', this.username, function (response) {
                    console.log(response);
                    // 注册成功
                    if (response) {
                        console.log(this.register);
                        this.register = false;
                        this.send = true;
                    }
                }.bind(this));
            }
        }
    });
</script>
</html>
