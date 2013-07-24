#!/usr/bin/perl
use DBI;
use DBD::mysql;
use Getopt::Std;
use XML::XPath;
use XML::XPath::XMLParser;

#Description:   Based off the input, this algorithm implements a recursive function in order to identify a port and its path that satisfies the requirements
#Programmer:    Matthew Chung
#Date:          3/28/2012
#CONFIG VARIABLES




my $platform            = "mysql";
my $database            = "thelist";
my $host                = "matthew-zend-dev.belairinternet.com";
my $port                = "3306";
my $user                = "zend_thelist";
my $password            = "WiGwOoU";
my $dsn                 = "dbi:mysql:$database:$host:$port";

#Define global source equipment ID so that when the algorithm returns to the starting point, it will stop
my $source_equipment_id = 0;

#GLOBAL VARIABLES
my $dbstore             = DBI->connect($dsn, $user, $password) or die "Could not connect to database: " . DBI->errstr;
my $service_point_id    = '';
my @paths;
my @interface_ids;
my $if_feature_defined  = 0;
my @if_features_required;

sub initialize
{
		#Define two parameters -i -f
		#Access variable by $opt_i and $opt_f
        getopt('if');
        if (defined $opt_i){
                my $xp;
                eval{
                	$xp				= XML::XPath->new(xml => $opt_i);
	                my $nodeSet     = $xp->find('interfaces/interface/@id');
	                foreach my $node ($nodeSet->get_nodelist){
	                        push(@interface_ids, $node->getNodeValue);
	
	                }                	
                	1;	
                }
                or do {
                	print "<error><description>Invalid XML for argument -i.</description></error>\n";
                	return;
                };

        }else{
                 die "Argument -i missing.  Please provide valid interfaces XML";
        }
        if (defined $opt_f){
                my $xp          = XML::XPath->new(xml => $opt_f);
                my $nodeSet     = $xp->find('interface_features/interface_feature/@id');
                foreach my $node ($nodeSet->get_nodelist){
                        push(@if_features_required, $node->getNodeValue);
                }
        $if_feature_defined     = 1;
        }


}
sub throw_xml_error
{
	my %args			= @_;
	my $description		= ${description};
	
	exit;
}
sub get_servicepoint_ports
{
        my %args                = @_;
        my $service_point_id    = $args{service_point_id};
        my $sql_query           = $dbstore->prepare(    "
                                                                SELECT i.if_id, (COUNT(ic2.if_id_b) + COUNT(ic1.if_id_a)) AS total_connections FROM interfaces i
                                                                LEFT OUTER JOIN interface_connections ic1
                                                                ON ic1.if_id_a = i.if_id
                                                                LEFT OUTER JOIN interface_connections ic2
                                                                ON ic2.if_id_b = i.if_id
                                                                WHERE i.service_point_id = '$service_point_id'
                                                                AND (ic1.if_id_a = i.if_id OR ic2.if_id_b = i.if_id)
                                                                GROUP BY i.if_id"
                                                        )
                                        or die "Couldn't prepare statement: " . $dbstore->errstr;
        $sql_query->execute()
                or die "Unable to execute query " . $dbstore->errstr;
        my @data;
        my @service_point_interfaces;
        while (@data = $sql_query->fetchrow_array()){
                        $interface_id   = $data[0];
                        push(@service_point_interfaces, $interface_id);
        }
        return @service_point_interfaces;
}

#Comment from Matt Chung
#Ideally, need to use some XML Library to build data structures instead of creating XML below
#this is error prone

sub create_path_xml
{
	my %args				= @_;
	my $interface_id		= $args{interface_id};
	my $feature_id			= $args{feature};
	my $paths_ref			= $args{paths};
	my @paths				= @{$paths_ref};
	#my $xml_node			= "<feature id='$feature_id' if_id='$interface_id'>\n";
	$xml_node				= '';

	foreach $ref_path (@paths){
		my @path					= @{$ref_path};
		my $hop_count				= scalar(@path);
		$xml_node			.= "\t<path hop_count='$hop_count' feature_id='$feature_id'>\n";
		my $i = 0;
		for ($i=0; $i < scalar(@path); $i++){
			my $path_interface_id	= $path[$i];
			my $path_index			= $i;
			$xml_node				.=  "\t\t<interface id='$path_interface_id' index='$path_index'/>\n";
		}
		$xml_node			.= "\t</path>\n";
	}
	#$xml_node 				.=  "</feature>\n";
	print $xml_node;
}

sub run
{
		print "<paths>\n";
        foreach (@interface_ids){
                if($if_feature_defined){
			
                        $source_equipment_id    = get_equipment_id(
                                                        interface_id    => $_
                                                                );
                        %obj_interface          = get_interface_object(
                                                        interface_id=> $_
                                                                        );
                        
                        
                        foreach my $required_feature (@if_features_required){
                        	
                        	#Reset global @paths variables
                        	@paths = ();
	                        #print ".....Beginning crawl for interface ID $_...... for feature $required_feature\n";
	                        my @path				= ($_);
	                        crawl_interface_feature(interface=> \%obj_interface, path => \@path, feature => $required_feature);
	                        create_path_xml(
	                        			interface_id 	=> $_,
	                        			feature			=> $required_feature,
	                        			paths			=> \@paths,
	                        				);
	                        			
	                                                	
                        }

                }

        }
        print "</paths>\n";
        
        
}
#Returns false if interface does not support feature
#Retruns 'true' if interface supports feature
#Required paramters: interface, feature_id
sub supports_feature
{
	my %args								= @_;
	my $ref_interface						= $args{interface};
	my $feature_id							= $args{feature};
	%connected_interface_obj				= %{$ref_interface};
	$ref_connected_interface_features		= $connected_interface_obj{features};
	%connected_interface_features			= %{$ref_connected_interface_features};	
	if (exists $connected_interface_features{$feature_id}){
		return 1;
	}
	else{

		return 0;
	}
}
sub originates_feature
{
	my %args								= @_;
	my $ref_interface						= $args{interface};
	my $feature_id							= $args{feature};
	%connected_interface_obj				= %{$ref_interface};
	$ref_connected_interface_features		= $connected_interface_obj{features};
	%connected_interface_features			= %{$ref_connected_interface_features};	
	if (defined $connected_interface_features{$feature_id}){
		return 1;
	}
	else{

		return 0;
	}
}
sub crawl_interface_feature
{
		my %args						= @_;
		my $ref_interface				= $args{interface};
		my $ref_path					= $args{path};
		my @path						= @{$ref_path};
        my %interface_hash              = %{$ref_interface};
   		my $required_interface_feature	= $args{feature};
        my @connected_interface_ids     = @{$interface_hash{connected_interfaces}};
        my $int_connected_interfaces    = scalar(@connected_interface_ids);

		#Is this interface connected to anything?
        if ($int_connected_interfaces){
                my $i = 0;
                
                #Loop through each of the connected interfaces
                for ($i = 0; $i < $int_connected_interfaces; $i++){
                	
                		

						#Get the interface ID of the other endpoint
                        my $connected_interface_id      = $connected_interface_ids[$i];

                        my %connected_interface_obj     = get_interface_object(
                                                                interface_id    => $connected_interface_id
                                                                                );
                        my $connected_interface_eq_id   = get_equipment_id(
                                                                interface_id    => $connected_interface_id
                                                                                );
                        my @other_connected_interfaces  = get_other_interfaces(
                                                                equipment_id    => $connected_interface_eq_id,
                                                                interface_id    => $connected_interface_id
                                                                
                                                                                );
                        $source_interface_id            = $interface_hash{interface_id};
                        
                        #implement here:
                        #if connected_interface does not support feature the return
                        #if connected_interface supports feature, check if it originates
                        #	a) does not originate
                        #			continue down the path
                        #	b) originate
                       	#			return

						#Add this interface_id to the path
                       	if (supports_feature(interface => \%connected_interface_obj, feature => $required_interface_feature)){						
							if(originates_feature(interface => \%connected_interface_obj, feature => $required_interface_feature)){
								#print "^^^^^^^$connected_interface_id originates value by this interface^^^^^^^^^^^\n";
								push(@path, $connected_interface_obj{interface_id});
								push(@paths, \@path);
								#print "Here is the entire path: " . "@path" . "\n";
								return;
							}else{
								#print "%%%%%%$connected_interface_id supports feature $required_interface_feature but does not originate itt%%%%%\n";
								my @new_path	= @path;
								push(@new_path, $connected_interface_obj{interface_id});
																
		                        foreach $other_connected_interface (@other_connected_interfaces){
	
		                                $other_connected_interface_eq_id        = get_equipment_id(
		                                                                                interface_id    => $other_connected_interface
		                                                                                        );
				                                                                                        
		                                if($other_connected_interface_eq_id ne $source_equipment_id){
		                                        %other_connected_interface_obj  = get_interface_object(
		                                                                                        interface_id    => $other_connected_interface
		                                                                                        );
		                                        crawl_interface_feature(interface => \%other_connected_interface_obj, path=> \@new_path, feature=>$required_interface_feature);
		                                }
		                                else{
		                                		#print "Error: Not a valid path because we looped back to the original piece of equipment\n";
		                                        return;
		                                }
		                        }										
							}
						
					}
					else{
						return;
					}
                       	


								
								

							                			
                		
                		
                        

                }

        }	
}
sub crawl_interface
{
		my %args						= @_;
		my $ref_interface				= $args{interface};
		my $ref_path					= $args{path};
		my @path						= @{$ref_path};
        my %interface_hash              = %{$ref_interface};
   
        my @connected_interface_ids     = @{$interface_hash{connected_interfaces}};
        my $int_connected_interfaces    = scalar(@connected_interface_ids);
		
		#Is this interface connected to anything?
        if ($int_connected_interfaces){
                my $i = 0;
                
                #Loop through each of the connected interfaces
                for ($i = 0; $i < $int_connected_interfaces; $i++){
                	
                		

						#Get the interface ID of the other endpoint
                        my $connected_interface_id      = $connected_interface_ids[$i];
                        my %connected_interface_obj     = get_interface_object(
                                                                interface_id    => $connected_interface_id
                                                                                );
                        my $connected_interface_eq_id   = get_equipment_id(
                                                                interface_id    => $connected_interface_id
                                                                                );
                        my @other_connected_interfaces  = get_other_interfaces(
                                                                equipment_id    => $connected_interface_eq_id,
                                                                interface_id    => $connected_interface_id
                                                                
                                                                                );
                        $source_interface_id            = $interface_hash{interface_id};
                        
                        #implement here:
                        #if connected_interface does not support feature the return
                        #if connected_interface supports feature, check if it originates
                        #	a) does not originate
                        #			continue down the path
                        #	b) originate
                       	#			return
                       	
                       	
                       	if($if_feature_defined){
							foreach my $required_interface_feature (@if_features_required){
								
								if (supports_feature(interface => \%connected_interface_obj, feature => $required_interface_feature)){
									#Add this interface_id to the path
																		
									
									if(originates_feature(interface => \%connected_interface_obj, feature => $required_interface_feature)){
										print "^^^^^^^$connected_interface_id originates value by this interface^^^^^^^^^^^\n";
										push(@path, $connected_interface_obj{interface_id});
										print "Here is the entire path: " . "@path" . "\n";
										return;
									}else{
										print "%%%%%%$connected_interface_id supports feature $required_interface_feature but does not originate itt%%%%%\n";
				                        foreach $other_connected_interface (@other_connected_interfaces){

				                                $other_connected_interface_eq_id        = get_equipment_id(
				                                                                                interface_id    => $other_connected_interface
				                                                                                        );
				                        		my @new_path							= @path;
				                        		push(@new_path, $other_connected_interface);				                                                                                        
				                                if($other_connected_interface_eq_id ne $source_equipment_id){
				                                        %other_connected_interface_obj  = get_interface_object(
				                                                                                        interface_id    => $other_connected_interface
				                                                                                        );
				                                        crawl_interface(interface => \%other_connected_interface_obj, path=> \@new_path);
				                                }
				                                else{
				                                		print "Error: Not a valid path because we looped back to the original piece of equipment\n";
				                                        return;
				                                }
				                        }										
									}
									
								}
								else{
									return;
								}								

							}                			
                		}
                		
                        

                }

        }
}
sub get_other_interfaces
{
        my %args                        = @_;
        my $equipment_id                = $args{equipment_id};
        my $interface_id_to_remove      = $args{interface_id};
        my $str_sql_query               = "
                                                SELECT if_id FROM interfaces
                                                WHERE eq_id = '$equipment_id'";
        my $sql_query                   = $dbstore->prepare($str_sql_query)
                                                or die "Unable to prepare sql statement for get_other_interfaces" . $dbstore->errstr;
        $sql_query->execute()
                or die "Unable to execute";

        my @data;
        my @other_interfaces;
                while (@data = $sql_query->fetchrow_array()){
                if($data[0] ne $interface_id_to_remove){
                        push(@other_interfaces, $data[0]);
                }
        }

        return @other_interfaces;
}
sub get_interface_object
{
        my %args                	= @_;
        my $interface_id        	= $args{interface_id};
        my @connected_interfaces	= get_connected_interface_ids(
                                                interface_id    => $interface_id
                                                                );
		my %interface_features		= get_interface_features(
												interface_id	=> $interface_id
														);			
		my $ref_interface_features	= \%interface_features;																							                                                                
        #create a reference to the connected interface
        $ref_connected_interfaces   = \@connected_interfaces;

        my %hash_interface          = (
                                                interface_id            => $interface_id,
                                                connected_interfaces    => $ref_connected_interfaces,
                                                features				=> $ref_interface_features,
                                        );
        return %hash_interface;
}
sub get_equipment_id
{
        my %args                = @_;
        $interface_id           = $args{interface_id};
        my $equipment_id        = 0;
        if(defined $interface_id){
                $str_sql_query  = "
                                        SELECT eq_id FROM interfaces
                                        WHERE if_id = '$interface_id'";
                $sql_query      = $dbstore->prepare($str_sql_query);
                $sql_query->execute();
                my @data;
                while (@data = $sql_query->fetchrow_array()){
                        $equipment_id   = $data[0];
                        return $equipment_id;
                };
        }else{
                die "Interface ID is not defined ";
        }
}
sub get_interface_features
{
		my %args						= @_;
		my $interface_id				= $args{interface_id};
		my $str_sql_query				=  "SELECT 	ifeatures.if_feature_id,
													if_feature_value
											FROM interface_features ifeatures
												INNER JOIN interface_feature_mapping 
													ON ifeatures.if_feature_id = interface_feature_mapping.if_feature_id
											WHERE 	if_id = $interface_id";
		my $sql_query                 	= prepare_query(
												query	=> $str_sql_query
														);
		$sql_query->execute();
		my @data;
		my %interface_features;
		while (@data = $sql_query->fetchrow_array()){

			$interface_features{$data[0]}	= $data[1];
			
		}				
					
		return %interface_features;
		
															
}
sub prepare_query()
{
	my %args							= @_;
	$str_sql							= $args{query};
	$sql_query							= $dbstore->prepare($str_sql) or die "Unable to prepare query";
}
sub get_connected_interface_ids
{
        my %args                        = @_;
        my $interface_id                = $args{interface_id};
        my $str_sql_query_1             = "
                                                SELECT if_id_b FROM interface_connections
                                                WHERE if_id_a = '$interface_id'";
        my $sql_query_1                 = $dbstore->prepare($str_sql_query_1)
                                        or die "Could not prepare statemetn: " . $dbstore->errstr;
        $sql_query_1->execute() or die "Unable to execute query" . $dbstore->errstr;

        my @data;
        my @connected_interfaces;


        while (@data = $sql_query_1->fetchrow_array()){
                $connected_interface            = $data[0];
                push(@connected_interfaces, $connected_interface);
        };


        my $str_sql_query_2             = "
                                                SELECT if_id_a FROM interface_connections
                                                WHERE if_id_b = '$interface_id'";
        my $sql_query_2                 = $dbstore->prepare($str_sql_query_2)
                                        or die "Could not prepare SQL QUERY 2 for interface_a " . $dbstore->errstr;

        $sql_query_2->execute() or die "Unable to execute query" . $dbstore->errstr;


        while (@data = $sql_query_2->fetchrow_array()){
                $connected_interface            = $data[0];
                push(@connected_interfaces, $connected_interface);
        };

        return @connected_interfaces;


}
initialize();
run();
        
        
