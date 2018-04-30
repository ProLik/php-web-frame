/**
 * Created by pc on 2018/4/30.
 */
DPS.regist("Demo.Index");
Demo.Index = function () {
};
Demo.Index.prototype = {
    'init':function(){
        $(document).ready(function(){
            alert(1);
        });
       alert(2);
    }
}