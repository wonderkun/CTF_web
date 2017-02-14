function Waterfall(param) {
    this.id = typeof param.container == 'string' ? document.getElementById(param.container) : param.container;
    this.colWidth = param.colWidth;
    this.colCount = param.colCount || 4;
    this.cls = param.cls && param.cls != '' ? param.cls : 'wf-cld';
    this.init();
}
Waterfall.prototype = {
    getByClass: function (cls, p) {
        var arr = [], reg = new RegExp("(^|\\s+)" + cls + "(\\s+|$)", "g");
        var nodes = p.getElementsByTagName("*"), len = nodes.length;
        //alert(len);
        for (var i = 0; i < len; i++) {
            if (reg.test(nodes[i].className)) {
                arr.push(nodes[i]);
                reg.lastIndex = 0;
            }
        }
        return arr;
    },
    maxArr: function (arr) {
        var len = arr.length, temp = arr[0];
        for (var ii = 1; ii < len; ii++) {
            if (temp < arr[ii]) {
                temp = arr[ii];
            }
        }
        return temp;
    },
    getMar: function (node) {
        var dis = 0;
        if (node.currentStyle) {
            dis = parseInt(node.currentStyle.marginBottom);
        } else if (document.defaultView) {
            dis = parseInt(document.defaultView.getComputedStyle(node, null).marginBottom);
            //alert(dis);
        }
        return dis;
    },
    getMinCol: function (arr) {
        var ca = arr, cl = ca.length, temp = ca[0], minc = 0;
        for (var ci = 0; ci < cl; ci++) {
            if (temp > ca[ci]) {
                temp = ca[ci];
                minc = ci;
            }
        }
        return minc;
    },
    init: function () {
        var _this = this;
        var col = [], //列高
            iArr = []; //索引
        var nodes = _this.getByClass(_this.cls, _this.id), len = nodes.length;
        //alert(len);
        for (var i = 0; i < _this.colCount; i++) {
            col[i] = 0;
        }
        for (var i = 0; i < len; i++) {
            //alert(_this.getMar(nodes[i]));
            //alert(nodes[i]);
            nodes[i].h = nodes[i].offsetHeight + _this.getMar(nodes[i]);
            //alert(nodes[i].h);
            iArr[i] = i;
        }
        var isIE = !!window.ActiveXObject;
        var isIE8 = isIE && !!document.documentMode;
        for (var i = 0; i < len; i++) {
            var ming = _this.getMinCol(col);
            nodes[i].style.left = ming * _this.colWidth + "px";
            nodes[i].style.top = col[ming] + "px";
            if (isIE8) {
                col[ming] += nodes[i].h + 0;
            }
            else {
                col[ming] += nodes[i].h;
            }
            //alert(nodes[i].h);
        }
        //alert(_this.maxArr(col));
        _this.id.style.height = _this.maxArr(col) + "px";
    }
};
$(document).ready(function () {
    new Waterfall({
        "container": "wf-main",
        "colWidth": 238,
        "colCount": 4
    });

})