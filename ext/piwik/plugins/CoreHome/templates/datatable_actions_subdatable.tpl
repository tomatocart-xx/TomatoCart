<tr id="{$properties.uniqueId}"></tr>
{if isset($arrayDataTable.result) and $arrayDataTable.result == 'error'}
	{$arrayDataTable.message} 
{else}
	{if count($arrayDataTable) == 0}
	<tr><td colspan="{$nbColumns}">{'CoreHome_CategoryNoData'|translate}</td></tr>
	{else}
		{foreach from=$arrayDataTable item=row}
		<tr {if $row.idsubdatatable}class="subActionsDataTable" id="{$row.idsubdatatable}"{else}class="actionsDataTable"{/if}>
			{foreach from=$dataTableColumns item=column}
			<td>
			{if isset($row.columns[$column])}{$row.columns[$column]}{else}{$defaultWhenColumnValueNotDefined}{/if}
			</td>
			{/foreach}
		</tr>
		{/foreach}
	{/if}		
{/if}
