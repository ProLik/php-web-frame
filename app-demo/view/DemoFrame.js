DPS.regist("DemoFrame");
DemoFrame = function () {


    this.init = function () {

       alert(6);
    };
    
}


/**
 * 获取宽度
 * @returns int
 */
DemoFrame.getScreenWidth = function(){
    return $(document.body).width();
};

/**
 * 获取高度
 * @returns int
 */
DemoFrame.getScreenHeight = function(){
    return $(document.body).height();
};


/**
 * 获取真是尺寸
 * @param 原始尺寸
 * @param 设计稿高度
 * @returns int
 */
DemoFrame.getRealPx = function(px,psdWidth){
    psdWidth = psdWidth || 1080;
    return  Math.round(DemoFrame.getScreenWidth()/psdWidth*px);
};