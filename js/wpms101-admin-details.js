console.log('details')
if(document.querySelector('table.sites')){
	const sitesTable = document.querySelector('table.sites');
	const siteUrls = document.querySelectorAll('td.column-blogname strong a');
	siteUrls.forEach((div) => {
	  console.log(div.href)
	});
}