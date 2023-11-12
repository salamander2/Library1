//From https://www.cssscript.com/demo/number-spinner-quantity-input/
export default class QuantityInput{
	constructor(self,decreaseText,increaseText){
		this.input=document.createElement('input');
		this.input.value="M";
		this.input.type='text';
		this.input.name='title2';
		this.input.id='title2';
		this.input.size='1';
		//this.input.pattern='[A-Z][a-z]'; //this does not work!!!
		this.decreaseText=decreaseText||'Prev. Letter';
		this.increaseText=increaseText||'Next Letter';

		function Button(text,className){
			this.button=document.createElement('button');
			this.button.type='button';
			this.button.innerHTML=text;
			this.button.title=className;
			this.button.classList.add(className);
			return this.button;
		}
		this.subtract=new Button(this.decreaseText,'sub');
		this.add=new Button(this.increaseText,'add');
		let intID, intID2;
		this.add.addEventListener('click',()=>this.change_quantity(1));
		this.add.addEventListener('mousedown',(e)=> {
			intID = setInterval(() => { this.change_quantity(1); }, 200);
		});
		this.add.addEventListener('mouseup',(e)=> { clearInterval(intID); });

		this.subtract.addEventListener('click',()=>this.change_quantity(-1));
		this.subtract.addEventListener('mousedown',(e)=> {
			intID2 = setInterval(() => { this.change_quantity(-1); }, 200);
		});
		this.subtract.addEventListener('mouseup',(e)=> { clearInterval(intID2); });

		self.appendChild(this.subtract);
		self.appendChild(this.input);
		self.appendChild(this.add);
	}
	change_quantity(change){
		let letter=this.input.value.toUpperCase();
    	letter=String.fromCharCode(letter.charCodeAt(0) + change);
	 	if (letter < "A") letter = "Z";
	 	if (letter > "Z") letter = "A";
			
		//if(isNaN(quantity))quantity=1;
		//quantity+=change;
		//quantity=Math.max(quantity,1);
		this.input.value=letter;
	}
}

/*
//if button is keeping pressed execute increment javascript
var timeout;
var speed = 500;
// Increment button
$('#plus-btn').on('mousedown mouseup mouseleave', e => {
  if (e.type == "mousedown") {
    increment(speed);
  } else {
    stop()
  }
});
// Increment function
function increment(speed) {
  $('#qty-input').val(parseInt($('#qty-input').val()) + 1);
  timeout = setTimeout(() => {
    increment(speed * 0.8);
  }, speed);
}
function stop() {
  clearTimeout(timeout);
}

*/
