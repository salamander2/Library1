/*******************************************************
* This code creates an input where the letter is entered. 
* It also creates two buttons, add and subtract. 
* These run code to increment/decrement the letter.
* If you hold + or - down, then the letters increment/decrement rapidly.
* It's all assembled and then added to the <div> indicated when the function was called.
* Original code from  https://www.cssscript.com/demo/number-spinner-quantity-input/
********************************************************/
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
		this.input.value=letter;
	}
}
