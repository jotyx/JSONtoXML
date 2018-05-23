## JSONtoXML
Convert input JSON file to XML format

## RUN
`php JSONtoXML.php -input=filename --output=filename [options]`

## OPTIONS
- `-input=filename`	input file in JSON format with UTF-8 encoding  
- `--output=filename`	output XML text file  
- `-h=subst`	replace invalid chars  
- `-n`	do not generate XML header  
- `-r=root-element`		name of root element  
- `--array-name=array-element`	set custom name of element around array  
- `--item-name=item-element`	set custom name of items in array, default value is item  
- `-s`	values of type string will be transformed to text elements, instead attributes  
- `-i`	values of type number will be transformed to text elements, instead attributes  
- `-l`	values of literals (true, false, null) will be transformed to text elements, instead attributes  
- `-c`	turn on translation of problematic characters  
- `-a, --array-size`	in array element add attribute size with number of items in array  
- `-t, --index-items`	every item of array will have index attribute with its number  
- `--start=n`	initialisation of incremental counter for array items indexing (needs to be combined with --index-items,
or error 1 will occur) to integer number >= 0 (default value n = 1)  
- `--types`	element of every scalar value will contain an attribute with type of value ,f.e. integer, real, string or literal  
