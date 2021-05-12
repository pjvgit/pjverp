
<iframe src="{{($file)??''}}" id='myFrame' height="100%" width="100%" > </iframe>
<script type="text/javascript">
		let objFra = document.getElementById('myFrame');
        objFra.contentWindow.focus();
        objFra.contentWindow.print();
</script>
