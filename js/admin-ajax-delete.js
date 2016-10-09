/**
 * AJAX удаление данных
 */
function deleteRowCallback( index, countRows )
{
	//console.log('DELETE callback: ', index, countRows );	
		
	// массив ID для удаления
	var officeIDs = [];	
	
	// Пройдем по удаленным рядам
	for (var i = index; i <= countRows; i++) 
	{
		var deletedRow = window[dataId][i];
		//console.log('DELETE post id: ', deletedRow.id );
		officeIDs.push(deletedRow.id);
	}
	
	//console.log('DELETE ids: ', officeIDs );
	if (officeIDs.length == 0) return;
	
	// Данные для AJAX запроса
	var data = { 
		action	: deleteAction,
		ids		: JSON.stringify(officeIDs)
	};

	// Запрос
	$('#' + containerId + ' .animationProcess').show();
	$.post(ajaxurl, data, function(response) {
        currentTable.handsontable('render');
		$('#' + containerId + ' .animationProcess').hide();
	});		
}
 