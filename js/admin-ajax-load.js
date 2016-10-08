/**
 * Загрузка данных AJAX
 */
$('#' + containerId + ' .loadButton').on('click', function(){
	// Данные для AJAX запроса
	var data = { action : loadAction };		
	// Читаем ВСЕ контролы, имеющие ID в fieldGroup и добавляем их в передаваемые данные
	$('#' + containerId + ' .fieldGroup *').each(function(i,obj){
			try{
				var control = $(obj);
				var id = control.attr('id');
				if (id) data[id] = control.val();
			}
			catch (err) {}
	});

	// Очистим поиск
	$('#' + containerId + ' .search').val('');
	// Запрос
	$('#' + containerId + ' .animationProcess').show();
	$.post(ajaxurl, data, function(response) {
		var data = jQuery.parseJSON(response);
		window[dataId] = data;
        currentTable.handsontable('loadData', data);
        currentTable.handsontable('render');
		$('#' + containerId + ' .animationProcess').hide();
	});	
});