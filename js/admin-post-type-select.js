/**
 * Перезагрузка данных после смены типа пунктов
 */
$('#' + containerId + ' .office_type_select').on('change', function(){
	$('#' + containerId + ' .loadButton').click();
});