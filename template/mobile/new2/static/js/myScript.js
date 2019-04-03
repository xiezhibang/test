function ProcessBar(Parament) {
    var labe = Parament.labels;
    var dat = Parament.data;
    var fiColor = (Parament.fillColor) ? Parament.fillColor : "red";
    var strColor = (Parament.strokeColor) ? Parament.strokeColor : "red";
    var poiColor = (Parament.pointColor) ? Parament.pointColor : "red";
    var poStrColor = (Parament.pointStrokeColor) ? Parament.pointStrokeColor
        : "red";
    var sGridLines = (Parament.scaleShowGridLines) ? Parament.scaleShowGridLines
        : false;
    var gridLineColor = (Parament.scaleGridLineColor) ? Parament.scaleGridLineColor
        : "red";
    var CanWidth = (Parament.CanvasWidth) ? Parament.CanvasWidth : 400;
    var CanHeight = (Parament.CanvasHeight) ? Parament.CanvasHeight : 200;
    var backColor = (Parament.BackColor) ? Parament.BackColor : "grey";
    var top = (Parament.Top) ? Parament.Top : "100px";
    var left = (Parament.Left) ? Parament.Left : "100px";

    this.Container = $("<div>");
    this.Container.addClass("Container");
    this.Container.css("background", backColor);
    this.Container.css("top", top);
    this.Container.css("left", left);

    this.canvas = document.createElement('canvas');
    this.canvas.width = CanWidth;
    this.canvas.height = CanHeight;
    this.ctx = this.canvas.getContext("2d");
    this.Container.append(this.canvas);

    this.data = {
        labels : labe,// arr,
        datasets : [ {
            fillColor : fiColor, // 线下颜色
            strokeColor : strColor, // 线的颜色
            pointColor : poiColor, // 数据点的颜色
            pointStrokeColor : poStrColor, // 数据点线圈的颜色
            data : dat
            // val
            // 特殊说明： null 会平滑度过，""等同于0,但不展示
        } ]
    }

    this.option = {
        // Boolean - 图标是否显示网格线 (默认值：true)
        scaleShowGridLines : sGridLines,
        // String - 网格线的颜色(默认值："rgba(0,0,0,.05)")
        scaleGridLineColor : gridLineColor,
        // Number - 网格线的宽度 (默认值：1）
        scaleGridLineWidth : 1,
        // Boolean - 点与点之间的连线是否为曲线（true：曲线，false：直线） (默认值：true）
        bezierCurve : true,
        // Number - 链接线的弯曲程度(0为直线)(默认值：0.4）
        bezierCurveTension : 0,
        // Boolean - 是否显示数据点(默认值：true）
        pointDot : false,
        // Number - 数据点内圆的大小(像素)(默认值：4）
        pointDotRadius : 4,
        // Number - 数据点外环的宽度(像素)(默认值：1）
        pointDotStrokeWidth : 1,
        // Number - 显示鼠标左右多少像素以内的数据点(默认值：20）
        pointHitDetectionRadius : 20,
        // Boolean - 数据集行程(默认值：true）
        datasetStroke : true,
        // Number - 链接线的宽度(默认值：20）
        datasetStrokeWidth : 6,
        // Boolean - 是否填充数据集(默认值：true）
        datasetFill : false,
    }
    new Chart(this.ctx).Line(this.data, this.option);

    this.upDate = function(x,y) {
        this.data = {
            labels : x,
            datasets : [ {
                fillColor : fiColor, // 线下颜色
                strokeColor : strColor, // 线的颜色
                pointColor : poiColor, // 数据点的颜色
                pointStrokeColor : poStrColor, // 数据点线圈的颜色
                data : y
            } ]
        }
        new Chart(this.ctx).Line(this.data, this.option);
    }


}
