
	<table width="100%" cellpadding="0" cellspacing="0" border="1" class="element_table">
		<thead>
			{thead}
				<tr>{cells}<td align="left">{value}</th>{/cells}</td>
			{/thead}
		</thead>
		<tbody>
			{tbody}
				<tr>{cells}<td align="left">{value}</td>{/cells}</tr>
			{/tbody}
		</tbody>
	</table>
	

<style>

table.element_table {
	border-collapse: collapse;
	border-bottom: 1px solid #D1D5DE;
	border-left: 1px solid #D1D5DE;	
	padding: 0;
	margin: 0 !important;
}

table.element_table tr td{
	border-top: 1px solid #D1D5DE;
	border-right: 1px solid #D1D5DE;
	background: white;
	color: black;
	height: 30px;
	padding: 0 10px;
}

table.element_table thead td
{
	background: #ecf1f4 url({ce_theme_folder_url}third_party/content_elements/elements/table/header_bg.jpg);
	font-weight: bold;
}

</style>