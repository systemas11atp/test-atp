{**
	* 2007-2017 PrestaShop
	*
	* NOTICE OF LICENSE
	*
	* This source file is subject to the Academic Free License 3.0 (AFL-3.0)
	* that is bundled with this package in the file LICENSE.txt.
	* It is also available through the world-wide-web at this URL:
	* https://opensource.org/licenses/AFL-3.0
	* If you did not receive a copy of the license and are unable to
	* obtain it through the world-wide-web, please send an email
	* to license@prestashop.com so we can send you a copy immediately.
	*
	* DISCLAIMER
	*
	* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
	* versions in the future. If you wish to customize PrestaShop for your
	* needs please refer to http://www.prestashop.com for more information.
	*
	* @author    PrestaShop SA <contact@prestashop.com>
	* @copyright 2007-2017 PrestaShop SA
	* @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
	* International Registered Trademark & Property of PrestaShop SA
	*}

	<div class="webchat" id="webchat">
		<div class="contenedorWebchat">
			<div class="open-button">
				<div>
					<svg width="20" height="20" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" class="icon_chat">
						<image href="https://avanceytec.com.mx/Avance-Circulo.png"  height="20" width="20" />
					</svg>
				</div>
				<label id="formTitle" onclick="openForm('expand')"><b>Chat Avance</b></label>
				<div id="cierre" class="circulo cierre" onclick="closeSkill()" style="display: none;">
					<i class="fas fa-times"></i>
				</div>
				<div id="minimiza" class="circulo minimiza" style="display: none;" onclick="openForm('close')">
					<i class="far fa-window-minimize"></i>
				</div>
			</div>
			<div class="chat-popup" id="myForm">
				<div id="skillSelectionDiv">
					
				</div>
			</div>
			<form id="chatSubmitForm" class="form-container">
				<div class="scrollable-content">
					<div class="greetings-message"  id="greetings-message">
						Le recordamos que nuestro HORARIO DE ATENCIÓN es de Lunes a Viernes de 8:00 a 19:00 hrs.
						Hora del centro.
					</div>
					<div>
						<label for="customerName">Nombre *</label>
						<input type="text" id="customerName" name="customerName" placeholder="Nombre" 
						maxlength="100" onfocus="functionFocus(this.id)" onblur="functionBlur(value,this.id)" required>
						<br>
                            <!--    
                            <label for="customerLastName">Apellido *</label>
                            <input type="text" id="customerLastName" name="customerLastName" placeholder="Apellido" 
                                   maxlength="20" onfocus="functionFocus(this.id)" onblur="functionBlur(value,this.id)" required>
                            <br>
                        -->
                        <label for="customerMail">Correo Electronico *</label>
                        <input type="email" id="customerMail" name="customerMail" placeholder="Correo Electronico"
                        onfocus="functionFocus(this.id)" onblur="functionBlur(value,this.id)" required>
                        <br>
                        <label for="customerPhNumber">Numero Telefonico</label>
                        <input type="text" onkeypress="validate(event)" id="customerPhNumber" name="customerPhNumber" placeholder="555-555-5555" maxlength="10">
                    </div>
                    <div class="cancel-send-chat">
                    	<input id="skillSelector" name="skillSelected" type="hidden">
                    	<button type="button" class="btn cancel" onclick="closeSkill()">Regresar</button>
                    	<button type="submit" class="btn send">Comenzar</button>
                    </div>
                    <br>
                </div>
            </form>
        </div>
    </div>
    
    {literal}
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans+Condensed:wght@300&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a097d63864.js" crossorigin="anonymous"></script>
    <script src="https://apps.mypurecloud.com/widgets/9.0/cxbus.min.js"  onload="javascript:CXBus.configure({debug:false,pluginsPath:'https://apps.mypurecloud.com/widgets/9.0/plugins/'}); CXBus.loadPlugin('widgets-core');"></script>
    {/literal}
    {$rand = rand(100,10000)/100}
    <script src="../../webchat/webchat-genesys.js?v={$rand}"></script>
    <link rel="stylesheet" type="text/css" href="../../webchat/webchat-genesys.css?v={$rand}">
    <script type="text/javascript">
    	function validate(evt) {
    		var theEvent = evt || window.event;

            // Handle paste
            if (theEvent.type === 'paste') {
            	key = event.clipboardData.getData('text/plain');
            } else {
                // Handle key press
                var key = theEvent.keyCode || theEvent.which;
                key = String.fromCharCode(key);
            }
            var regex = /[0-9]/;
            if (!regex.test(key)) {
            	theEvent.returnValue = false;
            	if (theEvent.preventDefault) theEvent.preventDefault();
            }
        }
    </script>

    <script type="text/javascript">
    	function functionBlur(val,id) {
    		var input = document.getElementById(id);
    		if (val==='') {
    			input.classList.add('invalid');
    			input.placeholder='Campo Requerido';
    		}
    	};

    	function functionFocus(id) {
    		var input = document.getElementById(id);
    		console.log(input.id);
    		if (input.classList.contains('invalid')) {
    			input.classList.remove('invalid');
    			if(input.id=='customerName'){
    				input.placeholder='Nombre';
    			}
    			else if(input.id=='customerLastName'){
    				input.placeholder='Apellido';
    			}
    			else if(input.id=='customerMail'){
    				input.placeholder='Correo Electronico';
    			}
    			else if(input.id=='customerPhNumber'){
    				input.placeholder='555-555-5555';
    			}
    		}
    	};
    </script>