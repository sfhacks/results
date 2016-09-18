/*
  pocketjs v1.0
  [http://anuv.me/pocketjs]
  File: pocket.js (pocketjs client)
  Source: [https://github.com/anuvgupta/pocketjs]
  License: MIT [https://github.com/anuvgupta/pocketjs/blob/master/LICENSE.md]
  Copyright: (c) 2016 Anuv Gupta
*/

var Pocket = function () {
    // data
    var ws;
    var ol = false;
    var id = 0;
    var address = '';
    var port = 0;
    var ev = {
        open: function () { },
        run: function () { },
        close: function () { },
    };
    var on = { };

    // object
    var pocket;
    pocket = {
        connect: function (domain, port, server) {
            var target = 'ws://' + domain + ':' + port + '/' + server;
            target = 'ws://' + domain + ':' + port.toString() + '/';
            if ('WebSocket' in window) ws = new WebSocket(target);
            else if ('MozWebSocket' in window) ws = new MozWebSocket(target);
            else {
                alert('WebSocket is not supported by this browser.');
                return;
            }
            ws.onopen = function (e) {
                console.log('[POCKET] connecting');
                ws.send(JSON.stringify({ command: 'alive', id: id }));
                return false;
            }
            ws.onclose = function (e) {
                ol = false;
                ws.send(JSON.stringify({ command: 'close', id: id, ad: address, p: port}));
                console.log('[POCKET] disconnected');
                ev['close']();
                return false;
            };
            ws.onmessage = function (e) {
                var data = JSON.parse(e.data);
                if (ol) {
                    if (data.args == null) pocket.call(data.call);
                    else pocket.callArr(data.call, data.args);
                } else {
                    id = data.id;
                    address = data.address;
                    port = data.port;
                    ol = true;
                    console.log('[POCKET] connected');
                    ev['open']();
                }
                // ws.send(JSON.stringify({ command: 'alive', id: id }));
                return false;
            };
            ws.onerror = function (e) {
                if (e.data == null) console.log('[POCKET] unknown error');
                else console.log('[POCKET] error: ' + e.data);
            };
            window.onbeforeunload = function () {
                console.log('[POCKET] closing');
                ws.onclose();
            };
        },
        send: function (n) {
            if (ol) {
                var data = { call: n };
                var args = [].slice.apply(arguments).slice(1);
                if (args.length > 0) data.args = args;
                ws.send(JSON.stringify(data));
            } else console.log('[ERROR] pocket is offline/connecting - data cannot be sent');
        },
        bind: function (n, f) {
            if (Object.prototype.toString.call(f) == '[object Function]') on[n] = f;
            else console.log('[ERROR] bind() requires parameter 2 to be a function');
        },
        call: function (n) {
            if (n in on) {
                var args = [].slice.apply(arguments).slice(1);
                if (args.length > 0) on[n].apply(on[n], args);
                else on[n]();
            } else console.log("[ERROR] event '" + n + "' does not exist");
        },
        callArr: function (n, a) {
            if (n in on) on[n].apply(on[n], a);
            else console.log("[ERROR] event '" + n + "' does not exist");
        },
        onOpen: function () {
            var args = [].slice.apply(arguments);
            if (args.length > 0) {
                if (Object.prototype.toString.call(args[0]) == '[object Function]')
                    ev['open'] = args[0];
                else ev['open'].apply(ev['open'], args);
            } else ev['open']();
        },
        onRun: function () {
            var args = [].slice.apply(arguments);
            if (args.length > 0) {
                if (Object.prototype.toString.call(args[0]) == '[object Function]')
                    ev['run'] = args[0];
                else ev['run'].apply(ev['run'], args);
            } else ev['run']();
        },
        onClose: function () {
            var args = [].slice.apply(arguments);
            if (args.length > 0) {
                if (Object.prototype.toString.call(args[0]) == '[object Function]')
                    ev['close'] = args[0];
                else ev['close'].apply(ev['close'], args);
            } else ev['close']();
        },
        online: function () { return ol; },
        getID: function () { return id; },
        getAddress: function () { return address; },
        getPort: function () { return port; }
    };
    return pocket;
};
