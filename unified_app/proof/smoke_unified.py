import os
import requests

base_url = os.environ.get('MBS_BASE_URL', 'http://127.0.0.1:8010').rstrip('/')
month_cycle = os.environ.get('MBS_SMOKE_MONTH', '2026-03')

print('base_url', base_url)
print('health', requests.get(base_url + '/health').status_code)
print('monthly-summary', requests.get(base_url + '/reports/monthly-summary', params={'month_cycle': month_cycle}).status_code)
print('recovery', requests.get(base_url + '/reports/recovery', params={'month_cycle': month_cycle}).status_code)
print('van', requests.get(base_url + '/reports/van', params={'month_cycle': month_cycle}).status_code)
print('excel', requests.get(base_url + '/export/excel/monthly-summary', params={'month_cycle': month_cycle}).status_code)
print('pdf', requests.get(base_url + '/export/pdf/monthly-summary', params={'month_cycle': month_cycle}).status_code)
