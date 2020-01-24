var chaato = new Chaato();

function Chaato() {
    // Vars
    var self = this;
    
    var jsonResult;
    
    var canvas;
    var canvasWidth;
    var canvasHeight;
    var context;
    
    var labelSpace;
    
    var itemMax;
    var itemMaxKey;
    var itemSpace;
    var itemPrevious;
    var listItems;
    
    var circleRange;
    
    var mousePosition;
    
    var backgroundType;
    
    var animationCount;
    var animationSpeed;
    
    var padding;
    var translate;
    var scale;
    
    // Properties
    self.setBackgroundType = function(value) {
        backgroundType = value;
    };
    
    self.setAnimationSpeed = function(value) {
        animationSpeed = value;
    };
    
    self.setPadding = function(value) {
        padding = value;
    };
    
    self.setTranslate = function(value) {
        translate = value;
    };
    
    self.setScale = function(value) {
        scale = value;
    };
    
    // Functions public
    self.init = function() {
        jsonResult = {};
        
        canvas = null;
        canvasWidth = 0;
        canvasHeight = 0;
        context = null;
        
        labelSpace = 0;
        
        itemMax = 0;
        itemMaxKey = -1;
        itemSpace = 0;
        itemPrevious = [];
        listItems = [];
        
        circleRange = 2;
        
        backgroundType = "grid";
        
        animationCount = 0;
        animationSpeed = 0.50;
        
        mousePosition = {};
        
        padding = 30;
        translate = [95, 20];
        scale = [0.91, 0.88];
    };
    
    self.create = function(json) {
        jsonResult = json;
        
        canvas = $(".graph_container").find(".canvas")[0];
        canvasWidth = canvas.width;
        canvasHeight = canvas.height;
        context = canvas.getContext("2d");

        labelSpace = Math.floor(canvasWidth / jsonResult.label.items.length);

        var tmpItemMax = [];

        $.each(jsonResult.elements, function(key, value) {
            tmpItemMax[key] = Math.max.apply(null, jsonResult.elements[key].items);
        });
        
        itemMax = Math.max.apply(null, tmpItemMax);
        itemMaxKey = indexOfMax(tmpItemMax);
        itemSpace = Math.floor(canvasHeight / jsonResult.elements[itemMaxKey].items.length) - 1;

        render();

        $(canvas).on("mousemove", "", function(event) {
            mousePosition = findMousePosition("canvas", event);

            info();
        });
    };
    
    // Functions private
    function render() {
        context.translate(translate[0], translate[1]);
        context.scale(scale[0], scale[1]);
        
        axes();
        
        background();
        
        $.each(jsonResult.elements, function(key, value) {
            $.each(value, function(keySub, valueSub) {
                if (keySub === "items") {
                    listItems[key] = [];
                    
                    dataLine(key, jsonResult.elements[key].color, valueSub);
                }
            });
        });
    }
    
    function axes() {
        context.beginPath();
        
        context.lineWidth = 1;
        context.strokeStyle = "#000000";
        
        context.moveTo(0, canvasHeight);
        context.lineTo(canvasWidth - labelSpace, canvasHeight);
        
        context.lineWidth = 1;
        context.strokeStyle = "#000000";
        
        context.moveTo(0, 0);
        context.lineTo(0, canvasHeight);

        context.stroke();
    }
    
    function background() {
        var labelItems = jsonResult.label.items;
        
        // Days
        $.each(labelItems, function(key, value) {
            context.beginPath();
            
            context.font = "20px Arial";
            context.textAlign = "center";
            context.strokeStyle = "#000000";
            context.fillText(value, (key * labelSpace), canvasHeight + padding + 5);
            
            if (backgroundType === "grid" || backgroundType === "lineY") {
                context.lineWidth = 1;
                context.strokeStyle = "#e9e9e9";
                
                context.moveTo((key * labelSpace), 0);
                context.lineTo((key * labelSpace), canvasHeight);
            }
            
            context.stroke();
        });
        
        // Items
        var itemsLength = jsonResult.elements[itemMaxKey].items.length;
        
        var step = Math.ceil(itemMax / labelItems.length);
        
        if (step < 2)
            step = 1;
        else if (step === 2)
            step = 3;
        
        for (var a = 0; a <= (itemMax + step); a += step) {
            context.beginPath();

            context.font = "20px Arial";
            context.textAlign = "end";
            context.strokeStyle = "#000000";
            context.fillText(a, -padding, canvasHeight - ((a * itemSpace) / (itemMax / itemsLength)) + 5);

            if (backgroundType === "grid" || backgroundType === "lineX") {
                context.lineWidth = 1;
                context.strokeStyle = "#e9e9e9";

                context.moveTo(0, canvasHeight - ((a * itemSpace) / (itemMax / itemsLength)));
                context.lineTo(canvasWidth - labelSpace, canvasHeight - ((a * itemSpace) / (itemMax / itemsLength)));
            }

            context.stroke();
        }
    }
    
    function dataLine(index, color, items) {
        itemPrevious = [];
        
        if (animationSpeed === 0)
            dataLineStatic(index, color, items);
        else {
            animationCount = 0;
            
            dataLineAnimation(index, color, items, itemPrevious, animationCount);
        }
    }
    
    function dataLineStatic(index, color, items) {
        $.each(items, function(key, value) {
            context.beginPath();

            context.lineWidth = 1;
            context.strokeStyle = color;
            context.lineCap = "round";
            context.lineJoin = "round";

            var x = (key * labelSpace);
            var y = canvasHeight - ((value * itemSpace) / (itemMax / jsonResult.elements[itemMaxKey].items.length));
            
            context.moveTo(x, y);
            context.lineTo(itemPrevious[0], itemPrevious[1]);

            itemPrevious[0] = x;
            itemPrevious[1] = y;

            context.closePath();

            context.stroke();
            
            context.arc(x, y, 5, 0, circleRange * Math.PI);
            
            listItems[index][key] = [x, y];

            context.fill();
        });
    }
    
    function dataLineAnimation(index, color, items, itemPrevious, animationCount) {
        var x = (animationCount * labelSpace);
        var y = canvasHeight - (items[animationCount] * itemSpace / (itemMax / jsonResult.elements[itemMaxKey].items.length));
        
        var amount = 0;

        var interval = setInterval(function() {
            amount += animationSpeed;

            if (amount > 1)
                amount = 1;

            context.beginPath();

            context.lineWidth = 1;
            context.strokeStyle = color;
            context.lineCap = "round";
            context.lineJoin = "round";

            var lerpX = itemPrevious[0] + (x - itemPrevious[0]) * amount;
            var lerpY = itemPrevious[1] + (y - itemPrevious[1]) * amount;

            context.moveTo(itemPrevious[0], itemPrevious[1]);
            context.lineTo(lerpX, lerpY);

            context.closePath();

            context.stroke();

            if (lerpX === x && lerpY === y || itemPrevious.length === 0) { 
                clearInterval(interval);
                
                context.arc(x, y, 5, 0, circleRange * Math.PI);
                
                listItems[index][animationCount - 1] = [x, y];

                context.fill();

                itemPrevious[0] = x;
                itemPrevious[1] = y;
                
                if (animationCount < items.length)
                    dataLineAnimation(index, color, items, itemPrevious, animationCount);
            }
        }, 30);
        
        animationCount ++;
    }
    
    function findMousePosition(type, event) {
        var x = 0;
        var y = 0;
        
        if (type === "canvas") {
            var canvasBox = canvas.getBoundingClientRect();
            
            mouseX = event.clientX - canvasBox.left;
            mouseY = event.clientY - canvasBox.top;
            
            x = Math.floor((mouseX / canvasBox.width) * canvas.width);
            y = Math.floor((mouseY / canvasBox.height) * canvas.height);
        }
        else if (type === "client") {
            x = event.clientX - window.pageXOffset;
            y = event.clientY - window.pageYOffset;
        }

        return {
            'x': x,
            'y': y
        };
    }
    
    function info() {
        $(".graph_container").find(".info").css({top: event.offsetY + 20, left: event.offsetX + 20});
        $(".graph_container").find(".info").hide();
        
        $.each(listItems, function(key, value) {
            $.each(value, function(keySub, valueSub) {
                var pointX = (mousePosition.x - (translate[0] + circleRange)) / scale[0];
                var pointY = (mousePosition.y - (translate[1] + circleRange)) / scale[1];

                var differenceX = Math.pow(valueSub[0] - pointX, 2);
                var differenceY = Math.pow(valueSub[1] - pointY, 2);
                var distance = Math.floor(Math.sqrt(differenceX + differenceY));
                
                if (distance <= 10) {
                    $(".graph_container").find(".info p span").eq(0).text(jsonResult.label.name);
                    $(".graph_container").find(".info p span").eq(1).text(jsonResult.elements[key].name[keySub]);
                    $(".graph_container").find(".info p span").eq(2).text(jsonResult.elements[key].items[keySub]);
                    $(".graph_container").find(".info").show();
                }
            });
        });
    }
    
    function indexOfMax(elements) {
        if (elements.length === 0)
            return -1;

        var max = elements[0];
        var maxIndex = 0;

        for (var a = 1; a < elements.length; a ++) {
            if (elements[a] > max) {
                maxIndex = a;
                max = elements[a];
            }
        }
        
        return maxIndex;
    }
}