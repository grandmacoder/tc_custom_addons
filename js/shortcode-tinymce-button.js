(function() {
    tinymce.PluginManager.add('tc_shortcode_button', function( editor, url ) {
	
		url = url.replace('/js', '');
        editor.addButton( 'tc_shortcode_button', {
            title: 'Insert Shortcode',
            type: 'menubutton',
			image : url + '/img/red-button.png',
            menu: [
                {
                    text: 'Create Activity Text',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Insert shortcode details',
                            body: [ 
							{
                                type: 'textbox',
                                name: 'descrip',
                                label: 'Description:'
                            },
                            {
                                type: 'textbox',
                                name: 'order',
                                label: 'Order on page:',
                            },
                            {
                                type: 'listbox', 
                                name: 'width', 
                                label: 'Width of box:', 
                                'values': [
								    {text: 'narrow', value: 'narrow'},
                                    {text: 'wide', value: 'wide'},
                                    {text: 'medium', value: 'medium'},
                                ]
                            },
							{
                                type: 'listbox', 
                                name: 'style', 
                                label: 'Color:', 
                                'values': [
									{text: 'blue', value: ''},
                                    {text: 'red', value: 'red'},
                                    {text: 'purple', value: 'purple'},
									{text: 'green', value: 'green'}
                                ]
                            },
							{
                                type: 'textbox',
                                name: 'reveal',
                                label: 'Text to reveal for suggested answers(optional)'
                            },
							{
                                type: 'container',
                                name: 'hint',
                                html: 'Make sure to select the total number of activities<br>for this page underneath the <span style="font-weight:bold">more fields heading below</span>'
                            }], 
                            onsubmit: function( e ) {
                            editor.insertContent( '[createActivityText description="'+e.data.descrip+'" order="'+e.data.order+'" width="'+e.data.width+'" style="'+e.data.style + '" reveal= "' + e.data.reveal+ '"] [/createActivityText]');
                            }
                        });
                    }
                }, //end of create activity text
                {
                    text: 'Create Link Popup',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Insert shortcode details',
                            body: [{
                                type: 'textbox',
                                name: 'linktext',
                                label: 'Link text:',
								multiline: true,
								minWidth: 300,
								minHeight: 100
                            },
                            {
                                type: 'textbox',
                                name: 'order',
                                label: 'Order on page:',
                            },
                            {
                                type: 'listbox', 
                                name: 'height', 
                                label: 'Height of popup window:', 
                                'values': [
                                    {text: '800', value: '""'},
                                    {text: '1000', value: '1000'},
                                    {text: '12000', value: '1200'}                           
                                ]
                            },
							{
                                type: 'listbox', 
                                name: 'style', 
                                label: 'Color:', 
                                'values': [
									{text: 'brown', value: 'brown'},                            
									{text: 'green', value: 'green'}
								]                  
                            }],
                            onsubmit: function( e ) {
                                editor.insertContent( '[createLinkPopup link_text="'+e.data.linktext+'" order='+e.data.order+' width="'+e.data.height+'" style="'+e.data.style+'"]');
                            }
                        });
                    }
                }, // end of Create Link Popup 
				{
                    text: 'Create Image Popup',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Insert shortcode details',
                            body: [{
                                type: 'container',
                                name: 'hint',
                                html: 'Find the URL in your <span style="font-weight:bold">media manager.</span><br>Remember to fill out Pop up text content below post in <span style="font-weight:bold">more fields.</span>'
                            },
							{
                                type: 'textbox',
                                name: 'imageurl',
                                label: 'Image URL:'
                            },
                            {
                                type: 'textbox',
                                name: 'order',
                                label: 'Order on page:',
                            },
							{
                                type: 'listbox', 
                                name: 'alignbox', 
                                label: 'Position:',
                                'values': [
                                    {text: 'left', value: 'left'},
									{text: 'right', value: 'right'},
                                    {text: 'center', value: 'center'}
								]                  
                            }],
                            onsubmit: function( e ) {
                                editor.insertContent( '[createImagePopup link_image="'+e.data.imageurl+'" order='+e.data.order+' align="'+e.data.alignbox+'"]<div style="clear:both;"></div>');
                            }
                        });
                    }
                },// end of Create Image Popup
				{
                    text: 'Create Check List',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Insert shortcode details',
                            body: [{
                                type: 'container',
                                name: 'hint',
                                html: 'You will need the checklist name from your web developer'
                            },
							{
                                type: 'textbox',
                                name: 'checklist',
                                label: 'Check list name:'
                            }
                           ],
                            onsubmit: function( e ) {
                                editor.insertContent( '[createChecklist  checklist="'+e.data.checklist+'"]');
                            }
                        });
                    }
                }, //end of Create Check List 
				{
                    text: 'Get Activity Answer',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Insert shortcode details',
                            body: [{
                                type: 'container',
                                name: 'hint',
                                html: 'Enter the page id from activity answer.'
                            },
							{
                                type: 'textbox',
                                name: 'pageid',
                                label: 'Page Id:'
                            },
							{
                                type: 'textbox',
                                name: 'order',
                                label: 'Order on page:'
                            }
                           ],
                            onsubmit: function( e ) {
                                editor.insertContent( '[getActivityAnswer pageid='+e.data.pageid+' order='+e.data.order+']');
                            }
                        });
                    }
                }, //end of Get Activity Answer			
				{
                    text: 'Roll Text And Reveal',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Insert shortcode details',
                            body: [{
                                type: 'textbox',
                                name: 'clickontext',
                                label: 'Roll on text:',
								multiline: true,
								minWidth: 400,
								minHeight: 100
                            },
							{
                                type: 'textbox',
                                name: 'revealtext',
                                label: 'Reveal text:',
								
								multiline: true,
								minWidth: 400,
								minHeight: 100
                            },
							{
                                type: 'textbox',
                                name: 'order',
                                label: 'Order on page:'
                            },
							{
                                type: 'listbox', 
                                name: 'style', 
                                label: 'Color:', 
                                'values': [
									{text: 'orange', value: ''},
                                    {text: 'red', value: 'red'},
                                    {text: 'blue', value: 'blue'},
									{text: 'clear', value: 'clear'}									
                                ]
                            }
                           ],
                            onsubmit: function( e ) {
                                editor.insertContent( '[clickAndReveal click="'+e.data.clickontext+'" reveal="'+e.data.revealtext+'" order='+e.data.order+' style="'+e.data.style+'"]');
                            }
                        });
                    }
                }, // end of Click Text And Reveal
				{
                    text: 'Click Pic And Reveal',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Insert shortcode details',
                            body: [{
                                type: 'container',
                                name: 'hint',
                                html: 'Find the URL in your <span style="font-weight:bold">media manager.</span>'
                            },
							{
                                type: 'textbox',
                                name: 'imageurl',
                                label: 'Image URL:'
                            },
							{
                                type: 'textbox',
                                name: 'revealtext',
                                label: 'Reveal text:',
								
								multiline: true,
								minWidth: 400,
								minHeight: 100
                            },
							{
                                type: 'textbox',
                                name: 'order',
                                label: 'Order on page:'
                            },
							{
                                type: 'listbox', 
                                name: 'alignbox', 
                                label: 'Position:',
                                'values': [
                                    {text: 'left', value: 'left'},
									{text: 'right', value: 'right'},
                                    {text: 'center', value: 'center'}
								]                  
                            },
							{
								type:'checkbox',
								name:'openonly',
								label: 'Reveal text only:',
								text:'Yes', 
								value: 'yes'
							}
                           ],
                            onsubmit: function( e ) {
								var openOnlyCheck;
								openOnlyCheck = "";
								if(e.data.openonly === true){
									openOnlyCheck = 'yes';
								}
                                editor.insertContent('<div style="clear:both;"></div>[clickPicReveal click=&#39;<img src='+e.data.imageurl+' />&#39; reveal="'+e.data.revealtext+'" order="'+e.data.order+'" position="'+e.data.alignbox+'" openonly="'+openOnlyCheck+'"]');
                            }
                        });
                    }
                }, // end of Click Pic And Reveal
				{
                    text: 'Insert File Uploader',
                    onclick: function() {
                        editor.insertContent( '[insertFileUploader][/insertFileUploader]');  
					} 
				},//end of Insert File Uploader
				{
                    text: 'Show Uploaded Files',
                    onclick: function() {
                        editor.insertContent( '[showUploadedFiles][/showUploadedFiles]');  
					} 
				},//end of Show Uploaded Files
{
                    text: 'Insert Contact Link',
                    onclick: function() {
                        editor.windowManager.open( {
                            title: 'Insert shortcode details',
                            body: [{
                                type: 'textbox',
                                name: 'text',
                                label: 'Enter the text that goes with the link:',
								multiline: true,
								minWidth: 400,
								minHeight: 50
                            }
							],
                            onsubmit: function( e ) {
                                editor.insertContent( '[createContactLink text="'+e.data.text+'"]');
                            }
                        });
                    }
                }//end create a contact link				
           ] //end of menu
        });
    });
})();