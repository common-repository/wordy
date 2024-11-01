


function wordy_window_resize() 
{
	if (jQuery('#TB_window').hasClass('wordy'))
	{
		wordy_tbiframe_resize();
	}
	else
	{
	  tb_position();
  }
}



function wordy_tbiframe_resize() 
{
	var WordyPaymentWidth = 1000;

	var TB_newWidth = jQuery(window).width() < (WordyPaymentWidth + 40) ? jQuery(window).width() - 40 : WordyPaymentWidth;
	var TB_newHeight = jQuery(window).height() - 70;
	var TB_newMargin = (jQuery(window).width() - WordyPaymentWidth) / 2;
	
	jQuery('#TB_window').css('marginLeft', - (TB_newWidth / 2));
	jQuery('#TB_window, #TB_iframeContent').width(TB_newWidth).height(TB_newHeight);
}



function wordy_checktb() 
{
	jQuery('a#add_image').click(function(i, event)
	{
	  clearInterval(checkThickboxInit);
    wordy_tbshow();
	});
}



function wordy_tbshow() 
{
  tb_show('Wordy Payment', wordyPluginURL + '/views/iframe.html?TB_iframe=true');
}



function wordy_init_payment(event) 
{
	wordyPluginURL = event;

	jQuery(window).resize(function(i, event)
	{
		jQuery(window).bind('resize.wordy', wordy_window_resize);
	});

	checkThickboxInit = setTimeout(wordy_tbshow, 1000);
}



function wordyMoneyFormat(pAmount) 
{
	pAmount = Math.round(pAmount * 100) / 100;

	var minAmount = 3;
	
	vZeroFill = pAmount.toString();

  if (vZeroFill.indexOf('.') + 2 == vZeroFill.length)
  {
	  vZeroFill = vZeroFill + '0';
  }
  else if (vZeroFill.indexOf('.') == -1)
  {
		vZeroFill = vZeroFill + '.00';
  }
  
	vZeroFill = parseInt(vZeroFill);

	vZeroFill = (vZeroFill > minAmount) ? vZeroFill : minAmount;

  return vZeroFill;
}



function wordyCountWords(text) 
{
	var count = 0;

	cleantext = text.replace(/\s/g,' ').replace(/(&lt;([^&gt;]+)&gt;)/ig,"").replace(/(<([^>]+)>)/ig,"").split(' ');

	for (var i = 0; i < cleantext.length; i++) 
	{
		if (cleantext[i].length > 0)
		{
		  count++;
	  }
	}
	
	return count;
}



function wordyCountCost(wordprice) 
{
	if (jQuery('#wp-word-count').html() != '') 
	{
		if (jQuery('#wp-word-count-cost').length <= 0 )
		{
		  jQuery('#wp-word-count').append('<span id="wp-word-count-cost"></span>')
    }
    
		var richEditor = (typeof tinyMCE != "undefined") && !tinyMCE.activeEditor.isHidden();
		var wordCount = richEditor ? wordyCountWords(tinyMCE.activeEditor.getContent()) : wordyCountWords(jQuery('textarea#content').val());

		try
		{ 
		  wordCount = wordCount + wordyCountWords(jQuery('#excerpt').val()); 
		}
		catch(error) { }

		jQuery('#word-count').html(wordCount);

		if (wordprice > 0)
		{
			jQuery('#wp-word-count-cost').html(' / &asymp;&nbsp;&euro;' + wordyMoneyFormat(wordCount * wordprice));
	  }
	}
}



function wordyInitCountCost(wordprice) 
{
	setInterval(function() { wordyCountCost(wordprice); }, 500);
}