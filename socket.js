// HTTP
var http = require('http').Server();
// io
var io = require('socket.io')(http);

// 用户群
users = [];
// 链接
connections = [];

// 进入房间的用户
roomUser = {};


// 链接
io.sockets.on('connection', function (socket) {
    // 链接数
    connections.push(socket);
    console.log('链接数：', connections.length);


    // 进入房间的用户
    var room_username = '';

    // 获取用户当前的url，从而截取出房间id
    var url = socket.request.headers.referer;

    var split_arr = url.split('/');

    var roomid = split_arr[split_arr.length - 1] || 'public';

    // 加入房间
    socket.on('room new user', function (username, callback) {
        // 用户名
        room_username = username;

        // 将用户归类到房间
        if (!roomUser[roomid]) {
            roomUser[roomid] = [];
        }

        if (roomUser[roomid].indexOf(username) !== -1) {
            callback(false);
        } else {
            // 向指定房间加入用户
            roomUser[roomid].push(room_username);

            // 创建房间
            socket.join(roomid);

            // 给指定的房间返回消息
            socket.to(roomid).emit('room prompt', room_username + '加入了房间');

            // 发送
            socket.emit('room prompt', room_username + '加入了房间');
            // 返回true
            callback(true);
        }
    });

    // 监听来自客户端的消息
    socket.on('room send message', function (message) {
        // 验证如果用户不在房间内则不给发送
        if (roomUser[roomid].indexOf(room_username) < 0) {
            return false;
        }

        // 指定房间
        socket.to(roomid).emit('room new message', {'message': message, 'user': room_username});

        // 发送消息
        socket.emit('room new message', {'message': message, 'user': room_username});
    });

    // 关闭
    socket.on('disconnect', function () {
        // 从房间名单中移除
        socket.leave(roomid, function (error) {
            if (!room_username) {
                return;
            }

            if (error) {
                console.log(error);
            } else {
                // 用户是否在这个组里
                var index = roomUser[roomid].indexOf(room_username);

                if (index !== -1) {
                    roomUser[roomid].splice(index, 1);
                    socket.to(roomid).emit('room prompt', room_username + '退出了房间');
                }
            }
        });
    });
});

http.listen(3000);