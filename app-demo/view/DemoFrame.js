DPS.regist("DemoFrame");
DemoFrame = function () {


    this.init = function () {

       alert(6);
    };
    
}


/**
 * ��ȡ���
 * @returns int
 */
DemoFrame.getScreenWidth = function(){
    return $(document.body).width();
};

/**
 * ��ȡ�߶�
 * @returns int
 */
DemoFrame.getScreenHeight = function(){
    return $(document.body).height();
};


/**
 * ��ȡ���ǳߴ篸
 * @param ԭʼ�ߴ�
 * @param ��Ƹ�߶�
 * @returns int
 */
DemoFrame.getRealPx = function(px,psdWidth){
    psdWidth = psdWidth || 1080;
    return  Math.round(DemoFrame.getScreenWidth()/psdWidth*px);
};