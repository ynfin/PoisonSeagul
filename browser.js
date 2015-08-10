function browser(params){

	// set parameters if they are not given at implementation
	if(params==null)params={};
	if(params.contentsDisplay==null)params.contentsDisplay=document.body;
	if(params.currentPath==null)params.currentPath="";
	if(params.filter==null)params.filter="";
	if(params.loadingMessage==null)params.loadingMessage="Loading...";
	if(params.data==null)params.data="";


	var search=function(){
		// set default message to the configured loading message
		if(params.pathDisplay!=null)params.pathDisplay.innerHTML=params.loadingMessage;

		// post JSON object, by AJAX, containing the data accuired from htm page
		var f=typeof(params.filter)=="object"?params.filter.value:params.filter;
		var a=new Ajax();
		with (a){
			Method="POST";
			URL="search_dir.php";
			Data="path="+params.currentPath+"&filter="+f+"&data="+params.data;
			ResponseFormat="json";
			ResponseHandler=showFiles;
			Send();
		}
	}

	// trigger a new search if refresh button is enabled and clicked.
	if(params.refreshButton!=null)params.refreshButton.onclick=search;

	// show the files, this is the ResponseHandler from the AJAX object in search
	var showFiles=function(res){
		if(params.pathDisplay!=null){

			// fetch current path from res object
			var p=res.currentPath;

			// replace
			p=p.replace(/^(\.\.\/|\.\/|\.)*/g,"");

			// display the current path, and cut it with ... if too long.
			if(params.pathDisplay!=null){
				params.pathDisplay.title=p;
				if(params.pathMaxDisplay!=null){
					if(p.length>params.pathMaxDisplay)p="..."+p.substr(p.length-params.pathMaxDisplay,params.pathMaxDisplay);
				}
				params.pathDisplay.innerHTML="[Rt:\] "+p;
			}
		}

		// clear out the innerHTML of the content display.
		params.contentsDisplay.innerHTML="";

		// use oddeven for listing purposes
		var oddeven="odd";

		// iterate through res.contents gathered from AJAX response
		for (i=0;i<res.contents.length;i++){

			// store contents in f
			var f=res.contents[i];
			// create HTML element as el
			var el=document.createElement("p");
			// edit the created element, adding name, path, type (EDIT VISUALS HERE)
			with(el){
				setAttribute("title",f.fName);
				setAttribute("fPath",f.fPath);
				setAttribute("fType",f.fType);
				className=oddeven + " item ft_"+f.fType;
				innerHTML=f.fName;
			}
			// append it to the document as odd even classes, for CSS styling.
			params.contentsDisplay.appendChild(el);
			oddeven=(oddeven=="odd")?"even":"odd";
			el.onclick=selectItem;
		}
	}

	// on el.onclick, this will run to update the contents by a new search if dir
	var selectItem=function(){

		// fetch the name, type and path from the triggering element.
		var ftype=this.getAttribute("fType");
		var fpath=this.getAttribute("fPath");
		var ftitle=this.getAttribute("title");

		// configure opening of folders on select
		if(params.onSelect!=null)params.openFolderOnSelect=params.onSelect({"type":ftype,"path":fpath,"title":ftitle,"item":this},params);
		if(params.openFolderOnSelect==null)params.openFolderOnSelect=true;

		// if folder, set as new path, and conduct search...
		if(ftype=="folder" && params.openFolderOnSelect){
			params.currentPath=fpath;
			search();
		}
	}

	search();
}
