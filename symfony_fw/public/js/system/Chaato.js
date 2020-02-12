"use strict";

/* global */

class Chaato {
    // Properties
    set setBackgroundType(value) {
        this.backgroundType = value;
    }
    
    set setAnimationSpeed(value) {
        this.animationSpeed = value;
    }
    
    set setPadding(value) {
        this.padding = value;
    }
    
    set setTranslate(value) {
        this.translate = value;
    }
    
    set setScale(value) {
        this.scale = value;
    }
    
    // Functions public
    constructor() {
        this.jsonResult = {};
        
        this.canvas = null;
        this.canvasWidth = 0;
        this.canvasHeight = 0;
        this.context = null;
        
        this.labelSpace = 0;
        
        this.itemMax = 0;
        this.itemMaxKey = -1;
        this.itemSpace = 0;
        this.itemPrevious = [];
        this.listItems = [];
        
        this.circleRange = 2;
        
        this.backgroundType = "grid";
        
        this.animationCount = 0;
        this.animationSpeed = 0.50;
        
        this.mousePosition = {};
        
        this.padding = 30;
        this.translate = [95, 20];
        this.scale = [0.91, 0.88];
    }
    
    create = (json) => {
        this.jsonResult = json;
        
        this.canvas = $(".graph_container").find(".canvas")[0];
        this.canvasWidth = this.canvas.width;
        this.canvasHeight = this.canvas.height;
        this.context = this.canvas.getContext("2d");
        
        this.labelSpace = Math.floor(this.canvasWidth / this.jsonResult.label.items.length);
        
        let tmpItemMax = [];
        
        $.each(this.jsonResult.elements, (key, value) => {
            tmpItemMax[key] = Math.max.apply(null, this.jsonResult.elements[key].items);
        });
        
        this.itemMax = Math.max.apply(null, tmpItemMax);
        this.itemMaxKey = this.indexOfMax(tmpItemMax);
        this.itemSpace = Math.floor(this.canvasHeight / this.jsonResult.elements[this.itemMaxKey].items.length) - 1;
        
        this.render();
        
        $(this.canvas).on("mousemove", "", (event) => {
            this.mousePosition = this.findMousePosition("canvas", event);
            
            this.info(event);
        });
    }
    
    // Functions private
    render = () => {
        this.context.translate(this.translate[0], this.translate[1]);
        this.context.scale(this.scale[0], this.scale[1]);
        
        this.axes();
        
        this.background();
        
        $.each(this.jsonResult.elements, (key, value) => {
            $.each(value, (keySub, valueSub) => {
                if (keySub === "items") {
                    this.listItems[key] = [];
                    
                    this.dataLine(key, this.jsonResult.elements[key].color, valueSub);
                }
            });
        });
    }
    
    axes = () => {
        this.context.beginPath();
        
        this.context.lineWidth = 1;
        this.context.strokeStyle = "#000000";
        
        this.context.moveTo(0, this.canvasHeight);
        this.context.lineTo(this.canvasWidth - this.labelSpace, this.canvasHeight);
        
        this.context.lineWidth = 1;
        this.context.strokeStyle = "#000000";
        
        this.context.moveTo(0, 0);
        this.context.lineTo(0, this.canvasHeight);
        
        this.context.stroke();
    }
    
    background = () => {
        let labelItems = this.jsonResult.label.items;
        
        // Days
        $.each(labelItems, (key, value) => {
            this.context.beginPath();
            
            this.context.font = "20px Arial";
            this.context.textAlign = "center";
            this.context.strokeStyle = "#000000";
            this.context.fillText(value, (key * this.labelSpace), this.canvasHeight + this.padding + 5);
            
            if (this.backgroundType === "grid" || this.backgroundType === "lineY") {
                this.context.lineWidth = 1;
                this.context.strokeStyle = "#e9e9e9";
                
                this.context.moveTo((key * this.labelSpace), 0);
                this.context.lineTo((key * this.labelSpace), this.canvasHeight);
            }
            
            this.context.stroke();
        });
        
        // Items
        let itemsLength = this.jsonResult.elements[this.itemMaxKey].items.length;
        
        let step = Math.ceil(this.itemMax / labelItems.length);
        
        if (step < 2)
            step = 1;
        else if (step === 2)
            step = 3;
        
        for (let a = 0; a <= (this.itemMax + step); a += step) {
            this.context.beginPath();
            
            this.context.font = "20px Arial";
            this.context.textAlign = "end";
            this.context.strokeStyle = "#000000";
            this.context.fillText(a, -this.padding, this.canvasHeight - ((a * this.itemSpace) / (this.itemMax / itemsLength)) + 5);
            
            if (this.backgroundType === "grid" || this.backgroundType === "lineX") {
                this.context.lineWidth = 1;
                this.context.strokeStyle = "#e9e9e9";
                
                this.context.moveTo(0, this.canvasHeight - ((a * this.itemSpace) / (this.itemMax / itemsLength)));
                this.context.lineTo(this.canvasWidth - this.labelSpace, this.canvasHeight - ((a * this.itemSpace) / (this.itemMax / itemsLength)));
            }
            
            this.context.stroke();
        }
    }
    
    dataLine = (index, color, items) => {
        this.itemPrevious = [];
        
        if (this.animationSpeed === 0)
            this.dataLineStatic(index, color, items);
        else {
            this.animationCount = 0;
            
            this.dataLineAnimation(index, color, items, this.itemPrevious, this.animationCount);
        }
    }
    
    dataLineStatic = (index, color, items) => {
        $.each(items, (key, value) => {
            this.context.beginPath();
            
            this.context.lineWidth = 1;
            this.context.strokeStyle = color;
            this.context.lineCap = "round";
            this.context.lineJoin = "round";
            
            let x = (key * this.labelSpace);
            let y = this.canvasHeight - ((value * this.itemSpace) / (this.itemMax / this.jsonResult.elements[this.itemMaxKey].items.length));
            
            this.context.moveTo(x, y);
            this.context.lineTo(this.itemPrevious[0], this.itemPrevious[1]);
            
            this.itemPrevious[0] = x;
            this.itemPrevious[1] = y;
            
            this.context.closePath();
            
            this.context.stroke();
            
            this.context.arc(x, y, 5, 0, this.circleRange * Math.PI);
            
            this.listItems[index][key] = [x, y];
            
            this.context.fill();
        });
    }
    
    dataLineAnimation = (index, color, items, itemPrevious, animationCount) => {
        let x = (animationCount * this.labelSpace);
        let y = this.canvasHeight - (items[animationCount] * this.itemSpace / (this.itemMax / this.jsonResult.elements[this.itemMaxKey].items.length));
        
        let amount = 0;
        
        let intervalEvent = setInterval(() => {
            amount += this.animationSpeed;
            
            if (amount > 1)
                amount = 1;
            
            this.context.beginPath();
            
            this.context.lineWidth = 1;
            this.context.strokeStyle = color;
            this.context.lineCap = "round";
            this.context.lineJoin = "round";
            
            let lerpX = itemPrevious[0] + (x - itemPrevious[0]) * amount;
            let lerpY = itemPrevious[1] + (y - itemPrevious[1]) * amount;
            
            this.context.moveTo(itemPrevious[0], itemPrevious[1]);
            this.context.lineTo(lerpX, lerpY);
            
            this.context.closePath();
            
            this.context.stroke();
            
            if (lerpX === x && lerpY === y || itemPrevious.length === 0) { 
                clearInterval(intervalEvent);
                
                this.context.arc(x, y, 5, 0, this.circleRange * Math.PI);
                
                this.listItems[index][animationCount - 1] = [x, y];
                
                this.context.fill();
                
                itemPrevious[0] = x;
                itemPrevious[1] = y;
                
                if (animationCount < items.length)
                    this.dataLineAnimation(index, color, items, itemPrevious, animationCount);
            }
        }, 30);
        
        animationCount ++;
    }
    
    findMousePosition = (type, event) => {
        let x = 0;
        let y = 0;
        
        if (type === "canvas") {
            let canvasBox = this.canvas.getBoundingClientRect();
            
            this.mouseX = event.clientX - canvasBox.left;
            this.mouseY = event.clientY - canvasBox.top;
            
            x = Math.floor((this.mouseX / canvasBox.width) * this.canvas.width);
            y = Math.floor((this.mouseY / canvasBox.height) * this.canvas.height);
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
    
    info = (event) => {
        $(".graph_container").find(".info").css({top: event.offsetY + 20, left: event.offsetX + 20});
        $(".graph_container").find(".info").hide();
        
        $.each(this.listItems, (key, value) => {
            $.each(value, (keySub, valueSub) => {
                let pointX = (this.mousePosition.x - (this.translate[0] + this.circleRange)) / this.scale[0];
                let pointY = (this.mousePosition.y - (this.translate[1] + this.circleRange)) / this.scale[1];
                
                let differenceX = Math.pow(valueSub[0] - pointX, 2);
                let differenceY = Math.pow(valueSub[1] - pointY, 2);
                let distance = Math.floor(Math.sqrt(differenceX + differenceY));
                
                if (distance <= 10) {
                    $(".graph_container").find(".info p span").eq(0).text(this.jsonResult.label.name);
                    $(".graph_container").find(".info p span").eq(1).text(this.jsonResult.elements[key].name[keySub]);
                    $(".graph_container").find(".info p span").eq(2).text(this.jsonResult.elements[key].items[keySub]);
                    $(".graph_container").find(".info").show();
                }
            });
        });
    }
    
    indexOfMax = (elements) => {
        if (elements.length === 0)
            return -1;
        
        let max = elements[0];
        let maxIndex = 0;
        
        for (let a = 1; a < elements.length; a ++) {
            if (elements[a] > max) {
                maxIndex = a;
                max = elements[a];
            }
        }
        
        return maxIndex;
    }
}