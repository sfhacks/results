/*
  pocketjs v1.1
  [http://anuv.me/pocketjs]
  Copyright: (c) 2016 Anuv Gupta
  File: pocket.js (pocketjs client)
  Source: [https://github.com/anuvgupta/pocketjs]
  License: MIT [https://github.com/anuvgupta/pocketjs/blob/master/LICENSE.md]
*/

var Pocket;
Pocket = function () {
    // data
    var websocket;
    var online = false;
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
        connect: function (domain, port, server, secure) {
            var target = 'ws' + (secure != undefined && secure != null && secure === true ? 's' : '') + '://' + domain + ':' + port.toString() + '/' + server;
            if ('WebSocket' in window) websocket = new WebSocket(target);
            else if ('MozWebSocket' in window) websocket = new MozWebSocket(target);
            else {
                console.warn('WebSocket/MozWebSocket is not supported by this browser');
                return;
            }
            websocket.onopen = function (e) {
                console.log('[POCKET] connecting');
                websocket.send(Pocket.encode(JSON.stringify({ command: 'alive', id: id })));
                return false;
            }
            websocket.onclose = function (e) {
                online = false;
                websocket.send(Pocket.encode(JSON.stringify({ command: 'close', id: id, ad: address, p: port})));
                console.log('[POCKET] disconnected');
                console.log(ev);
                ev['close']();
                return false;
            };
            websocket.onmessage = function (e) {
                var data = JSON.parse(e.data);
                if (online) {
                    if (data.args == null) pocket.call(data.call);
                    else pocket.callArr(data.call, data.args);
                } else {
                    id = data.id;
                    address = data.address;
                    port = data.port;
                    online = true;
                    console.log('[POCKET] connected');
                    ev['open']();
                }
                // websocket.send(Pocket.encode(JSON.stringify({ command: 'alive', id: id })));
                return false;
            };
            websocket.onerror = function (e) {
                if (e.data == null) console.log('[POCKET] unknown error');
                else console.log('[POCKET] error: ' + e.data);
            };
            window.onbeforeunload = function () {
                console.log('[POCKET] closing');
                websocket.onclose();
            };
            return pocket;
        },
        send: function (n) {
            if (online) {
                var data = { call: n };
                var args = [].slice.apply(arguments).slice(1);
                if (args.length > 0) data.args = args;
                websocket.send(Pocket.encode(JSON.stringify(data)));
            } else console.log('[ERROR] pocket is offline/connecting - data cannot be sent');
            return pocket;
        },
        bind: function (n, f) {
            if (typeof f == 'function' && f instanceof Function) on[n] = f;
            else console.log('[ERROR] bind() requires parameter 2 to be a function');
            return pocket;
        },
        call: function (n) {
            if (n in on) {
                var args = [].slice.apply(arguments).slice(1);
                if (args.length > 0) on[n].apply(on[n], args);
                else on[n]();
            } else console.log("[ERROR] event '" + n + "' does not exist");
            return pocket;
        },
        callArr: function (n, a) {
            if (n in on) on[n].apply(on[n], a);
            else console.log("[ERROR] event '" + n + "' does not exist");
            return pocket;
        },
        onOpen: function () {
            var args = [].slice.apply(arguments);
            if (args.length > 0) {
                if (typeof args[0] == 'function' && args[0] instanceof Function)
                    ev['open'] = args[0];
                else ev['open'].apply(ev['open'], args);
            } else ev['open']();
            return pocket;
        },
        onRun: function () {
            var args = [].slice.apply(arguments);
            if (args.length > 0) {
                if (typeof args[0] == 'function' && args[0] instanceof Function)
                    ev['run'] = args[0];
                else ev['run'].apply(ev['run'], args);
            } else ev['run']();
            return pocket;
        },
        onClose: function () {
            var args = [].slice.apply(arguments);
            if (args.length > 0) {
                if (typeof args[0] == 'function' && args[0] instanceof Function)
                    ev['close'] = args[0];
                else ev['close'].apply(ev['close'], args);
            } else ev['close']();
            return pocket;
        },
        online: function () { return online; },
        getID: function () { return id; },
        getAddress: function () { return address; },
        getPort: function () { return port; }
    };
    return pocket;
};

// convenience
Pocket.encode = function (text) {
    return '~pocketjs~' + text + '~pocketjs~';
};
