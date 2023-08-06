import scrapy
import requests
from scrapy.exporters import JsonLinesItemExporter

class QuotesSpider(scrapy.Spider):
    name = "quotes"

    def __init__(self, *args, **kwargs):
        super(QuotesSpider, self).__init__(*args, **kwargs)
        self.start_urls = [kwargs.get('start_url')]

    def parse(self, response):
        image_selector = response.css(self.image)
        data = {
            'image': image_selector.css('::attr(src)').get(),
            'title': response.css(self.title).get(),
            'price': response.css(self.price).get(),
            'weight': response.css(self.weight).get(),
            'dimensions': response.css(self.dimensions).get()
        }

        if not data['price']:
            data['price'] = 125
        
        if not data['weight']:
            data['weight'] = '10kg'
        
        if not data['dimensions']:
            data['dimensions'] = '10*20 cm'

        with open('scraped_data_' + self.user_id + '.json', 'ab') as f:
            exporter = JsonLinesItemExporter(f)

            # Start exporting data
            exporter.start_exporting()

            # Export the data to the JSON file
            exporter.export_item(data)

            # Finish exporting
            exporter.finish_exporting()

        yield data
        
        # endpoint_url = 'http://localhost/hezare-plugin/wp-json/vishar/v1/scrapy-data'
        # headers = {'Content-Type': 'application/json'}
        
        # # Make the API request
        # response = requests.post(endpoint_url, json=data, headers=headers)
        
        # # Process the response
        # if response.status_code == 200:
        #     print('Data sent successfully.')
        # else:
        #     print('Error sending data:', response.text)