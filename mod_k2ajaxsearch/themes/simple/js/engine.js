var K2AJAXSearch = {};

dojo.declare("K2AJAXSearchsimple", K2AJAXSearchBase, {
  constructor: function(args) {
    this.resultboxTopOffset = -3;
  },
  
  getResultBoxAnimation: function(){
    if(this.fadeInResult){ //fade-in and down
      dojo.style(this.searchResultsMoovable, "height", this.innerHeight+"px");
      
      this.textBoxPos = dojo.position(this.searchForm, true);
      dojo.style(this.searchResultsMoovable, "opacity", 0);
      dojo.style(this.searchResultsMoovable, "top", '-10px');
      this.fadeInResult=0;
      return dojo.animateProperty({
        node: this.searchResultsMoovable, 
        properties: {
            opacity: 1,
            top: { end:0, units:"px" }
        }, 
        duration: 300
      }).play();
    }else{
      return dojo.animateProperty({
        node: this.searchResultsMoovable, 
        properties: {
          height: {start: dojo.style(this.searchResultsMoovable, 'height'), end: this.innerHeight}
        }, 
        duration: 500
      }).play();
    }
  },
  
  getCloseResultBoxAnimation: function(){
    return dojo.animateProperty({
      node: this.searchResultsMoovable, 
      properties: {
        opacity: 0, 
        top: { end:10, units:"px" }
      }, 
      duration: 300, 
      onEnd : dojo.hitch(this,'removeResults')
    }).play();
  },
  
  getCategoryLeftPosition: function(num){
    var categorySize = this.filterbuttonwidth*(this.searchCategories.length - num - 1) + dojo.marginBox(this.searchCategories[num]).w;
    var extraFieldSize = 0;

    if(this.showExtraFields && this.catchooserfirst)
      extraFieldSize = this.filterbuttonwidth*this.searchExtraFields.length + 1; 
     
    this.searchFormPosition = dojo.position(this.searchForm, true);
    
    return this.searchFormPosition.x+this.searchFormPosition.w-categorySize-extraFieldSize-1;
  },

  getExtraFieldLeftPosition: function(num){
    var extraFieldSize = 0;

    if(!this.catchooserfirst && !this.hideCatChooser)
      extraFieldSize += this.filterbuttonwidth * this.searchCategories.length;
 
    extraFieldSize += this.filterbuttonwidth*(this.searchExtraFields.length - num - 1) + dojo.marginBox(this.searchExtraFields[num]).w + 1;
              
    this.searchFormPosition = dojo.position(this.searchForm, true);

    return this.searchFormPosition.x+this.searchFormPosition.w-extraFieldSize-1;
  }
});
