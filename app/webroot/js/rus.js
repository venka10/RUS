function checkForEmptyValues(){
	try{
		fieldsToCheck = new Array('SiteSiteName',
			'SiteAbbreviation',
			'SiteSiteAddress',
			//'SiteLonLat',
			'SiteCounty',
			'SiteSchoolDistrict',
			'SiteCongressionalDistrict',
			//'SiteRwfCensusPopulation',
			'SiteNslpfTotalStudents',
			'SiteNslpfPercEligible'
		);
		no_blank_fields = 0;
		for( i=0; i<fieldsToCheck.length; i++){
			if($(fieldsToCheck[i]) && $(fieldsToCheck[i]).value==''){
			    node = document.createElement('DIV');
			    node.id=fieldsToCheck[i]+'DIV-RI';
			    node.innerHTML = 'Record incomplete';
			    if(!$(fieldsToCheck[i]+'DIV-RI')){
			    	$(fieldsToCheck[i]).parentNode.insertBefore(node,$(fieldsToCheck[i]));
				}
				no_blank_fields++;
			}
		}
	}catch(e){
		alert(e);
	}
	if(no_blank_fields > 0){
		return confirm("This record is not complete. Save and close anyway?");
	}else{
		return true;
	}
}