import requests, os, json

# Retrieve UCD data at IMPC with failed statuses

# global variables
results = {}
has_failed = False

# sends a GET request to the specified url
response = requests.get('http://api.mousephenotype.org/tracker/centre/xml?centre=Ucd')
#convert json to an object 
outputs = response.json()

#loop through the filenames
for output in outputs:
	fname = output['filename']
	url = os.path.join('http://api.mousephenotype.org/tracker/xml/',fname)
	# sends a GET request to the specified url
	response = requests.get(url)
	#convert json to an object
	fname_outputs = response.json()

	if url.find('specimen') != -1: # specimen xml files
		for specimen in fname_outputs:
			if specimen['status'] == 'failed': # record the file_content that has failed status
				results['specimen_id'] = specimen['id'] 
				results['specimen_status'] = specimen['status'] 
				results['specimen_filename'] = specimen['filename'] 
				results['specimen_logs'] = specimen['logs']
				#convert an object to a string
				print(json.dumps(results, indent=4))

	else: # experiment xml files
		for experiment in fname_outputs:
			for procedure in experiment['experimentProcedures']:
				if procedure['status'] == 'failed':
					has_failed = True
					break # exits out of the first enclosing foreach
			if has_failed: # record the file_content that has failed status
				results['experiment_id'] = experiment['id'] 
				results['experiment_status'] = experiment['status']
				results['experiment_filename'] = experiment['filename']
				results['experiment_logs'] = experiment['logs']
				results['experiment_procedures'] = {}
				for procedure in experiment['experimentProcedures']:
					results['experiment_procedures']['experiment_procedure_status'] = procedure['status']
					results['experiment_procedures']['experiment_procedure_name'] = procedure['experimentName']
					results['experiment_procedures']['experiment_procedure_specimen'] = procedure['specimen']
					results['experiment_procedures']['experiment_procedure_logs'] = procedure['logs']
					#convert an object to a string
					print(json.dumps(results, indent=4))
