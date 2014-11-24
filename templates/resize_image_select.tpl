	{formlabel label="Reduce Original Size" for="resize"}
	{forminput}
		<select name="resize" id="resize" class="form-control">
			<option value="">{tr}Don't Resize{/tr}</option>
			<option value="640">1/3 MegaPixel ( 640 x 480 )</option>
			<option value="1024">3/4 MegaPixel ( 1024 x 768 )</option>
			<option value="1280">1 MegaPixel ( 1280 x 1024 )</option>
			<option value="1600">2 MegaPixel ( 1600 x 1200 )</option>
			<option value="2048">3 MegaPixel ( 2048 x 1536 )</option>
			<option value="2272">4 MegaPixel ( 2272 x 1704 )</option>
			<option value="2560">5 MegaPixel ( 2560 x 1920 )</option>
			<option value="2800">6 MegaPixel ( 2800 x 2100 )</option>
			<option value="3000">7 MegaPixel ( 3000 x 2300 )</option>
			<option value="3264">8 MegaPixel ( 3264 x 2448 )</option>
		</select>
		{formhelp note="Select the size your image should be resized to. This will only have an effect on the image if it is larger than the selected size. If your image is smaller than the selected size, it will not be resized at all."}
	{/forminput}
