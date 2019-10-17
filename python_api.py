# Retrieve UCD data at IMPC

import os, json, urllib.request

# global variables
results = {}
has_failed = False
#open url
data = urllib.request.urlopen('http://api.mousephenotype.org/tracker/centre/xml?centre=Ucd').read()
#convert json to python object (list)
outputs = json.loads(data)

#loop through the filenames we got and work with each of them.
for output in outputs:
	fname = output['filename']
	url = os.path.join('http://api.mousephenotype.org/tracker/xml/',fname)
	#open url
	data = urllib.request.urlopen(url).read()
	#convert json to python object (list)
	fname_outputs = json.loads(data)

	if url.find('specimen') != -1: # specimen xml files
		for specimen in fname_outputs:
			if specimen['status'] == 'failed': # record the file_content that has failed status
				results['specimen_id'] = specimen['id'] 
				results['specimen_status'] = specimen['status'] 
				results['specimen_filename'] = specimen['filename'] 
				results['specimen_logs'] = specimen['logs']
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
					print(json.dumps(results, indent=4))
