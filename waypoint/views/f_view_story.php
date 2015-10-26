<style>
.icon-filter { background-image: url('<?=base_url()?>resources/images/toolbar/filter.png') !important;}
.icon-excel { background-image: url('<?=base_url()?>resources/images/toolbar/excel.gif') !important;}
.icon-print { background-image: url('<?=base_url()?>resources/images/toolbar/print.png') !important;}

.merah .x-grid-cell { background-color: #ffe2e2; color: red;} 
.kuning .x-grid-cell { background-color: #FFFFCC; color: #FF9900;} 
.label-aktif {
        background-color: #BC120B;
        color: #fff;
        text-align: center;
        font-weight: bold;
        display: block;
        width: 70px;
    }

    .label-non-aktif {
        background-color: #009900;
        color: #fff;
        text-align: center;
        font-weight: bold;
        display: block;
        width: 70px;
    }
	
	.label-chek {
        background-color: #EAD41A;
        color: #fff;
        text-align: center;
        font-weight: bold;
        display: block;
        width: 70px;
    }

</style> 

<script type="text/javascript" src="<?=base_url()?>resources/utils/id_date.js"></script>

<script type="text/javascript">
Ext.Loader.setConfig({enabled: true});
Ext.Loader.setPath('Ext.ux', '<?=base_url()?>resources/ext4/examples/ux/');    
Ext.require([
    'Ext.ux.form.SearchField',
    'Ext.data.*', 
    'Ext.grid.*',
    'Ext.window.MessageBox',
    'Ext.tip.*',
    'Ext.form.*'    
]);

var storeGps;
var gridGps;

Ext.onReady(function(){

    Ext.tip.QuickTipManager.init();
    Ext.define('data', {
        extend: 'Ext.data.Model',
        fields: ['datetime', 'location',  'speed',
                 'engine','lng','lat','odometer']
    });

    storeGps = Ext.create('Ext.data.Store', {
        pageSize: 50,
        model: 'data',
        remoteSort: true,
        proxy: {
            type: 'ajax',
            url:  '<?=base_url()?>index.php/waypoint/main_view_sarana/queryGetStoryGps',
            reader: {
                root: 'data',
                totalProperty: 'totalCount'
            },
            simpleSortMode: true
        },
        sorters: [{
            property: 'datetime',
            direction: 'ASC'
        }]
    });
    
    
    /*var groupingFeature = Ext.create('Ext.grid.feature.Grouping',{
        groupHeaderTpl: '{name} ({rows.length} Item{[values.rows.length > 1 ? "s" : ""]})'
    });*/
    
    gridGps = Ext.create('Ext.grid.Panel', {
            region: 'center',
            title: 'Story GPS',
            xtype: 'grid',
            cls: 'custom-first-last',
            //features: [groupingFeature],
            store: storeGps,
            
            dockedItems: [{
                dock: 'top',
                xtype: 'toolbar',
                items: [{
                    text: 'Filter Dinasan',
                    iconCls: 'icon-filter',
                    scope: this,
                    handler: function() {showFormFilter()}
                },'-',{
                    text: 'Download Excel',
                    iconCls: 'icon-excel',
                    handler: function(){exportExcelStory(nosar,vtdid,dt1,t1,minute1)}
                },'->','Search : ',{
                    width: 250,
                    xtype: 'searchfield',
                    store: storeGps
                }]
            }],
            
            columns:[{
                id: 'datetime',
                text: 'WAKTU GPS',
                dataIndex: 'datetime',
				align: 'center',
                width: 150
            },{
                id: 'location',
                text: 'POSISI GPS',
                dataIndex: 'location',
				align: 'center',
                width: 250
            },{
                id: 'speed',
                text: 'KECEPATAN (KM/JAM)',
                dataIndex: 'speed',
				align: 'center',
                width: 150
            },{
                id: 'engine',
                text: 'MESIN',
                dataIndex: 'engine',
				align: 'center',
                width: 80,
				renderer :  function(val){
                  if (val == "0") return '<label class="label-aktif">' + 'OFF' + '</label>';
                  else if (val == "1") return '<label class="label-non-aktif">' + 'ON' + '</label>';
                  else if (val == "2") return '<label class="label-chek">' + 'IDLE' + '</label>';
                }
                
            },{
                id: 'lng',
                text: 'LONGITUDE',
                dataIndex: 'lng',
				align: 'center', 
                width: 100
            },{
                id: 'lat',
                text: 'LATITUDE',
                dataIndex: 'lat',
				align: 'center',
                width: 100
            },{
                id: 'odometer',
                text: 'ODOMETER',
                dataIndex: 'odometer',
				align: 'center',
                flex: 1
            }],
        
            bbar: Ext.create('Ext.PagingToolbar',{
                store: storeGps,
                displayInfo: true,
                displayMsg: 'Displaying Data : {0} - {1} of {2}',
                emptyMsg: "No Display Data"
            })
            
            
    });
    
    Ext.create('Ext.container.Viewport', {
        layout: 'border',
        padding: '5',
        items: [gridGps]
    });
    
    //storeGps.loadPage(1);
    showFormFilter();  
});


// FILTER ======================================================================
var nosar;
var vtdid;
var dt1;
var t1;
var minute1;

var formFilter = Ext.widget('form', {
        layout: {
            type: 'vbox',
            align: 'stretch'
        },
        border: false,
        bodyPadding: 10,
	

        fieldDefaults: {
            labelAlign: 'top',
            labelWidth: 100,
            labelStyle: 'font-weight:bold'
        },

        items: [{
            xtype: 'combo',
            fieldLabel: 'No Sarana Baru ',
            name: 'nosar',
            id: 'filter_nosar',
            triggerAction: 'all',
            hideTrigger: true, 
            minChars: 1, 
			width: 300,
            allowBlank: false,
            displayField:'display',
            valueField:'value',
            store: { 
                fields: ['id','value','display'], 
                autoLoad: true,
                proxy: { 
                    type: 'ajax',
                    url: '<?=base_url()?>index.php/waypoint/main_view_sarana/getNosarlocotrack' 
                } 
            } 
        },{
            xtype: 'fieldcontainer',
            fieldLabel: 'Tanggal ',
            combineErrors: true,
            msgTarget : 'side',
            layout: 'hbox',
            defaults: {
                flex: 1,
                hideLabel: true
            },
            items: [
                
                {
                    xtype     : 'datefield',
                    name      : 'd1',
                    format    : 'd F Y',
                    //margin    : '0 5 0 0',					
                    allowBlank: false,
                    value     : '<?=date('Y-m-d')?>'
                },{
                    xtype     : 'timefield',
                    name      : 't1',
                    //margin    : '0 5 0 0',
                    format    : 'H:i:s', 
                    allowBlank: false,
					width     : 50, 
                    value     : '00:00:00'
                }
            ]
        },{
             xtype:'combo',
             fieldLabel:'jumlah menit',
             name:'kel_minute',
			 //id:'kel_minute',
             displayField:'name',
             valueField:'value',
			 allowBlank: false,
             queryMode: 'local',
             store:Ext.create('Ext.data.Store', {
                 fields : ['name', 'value'],
                 data   : [
                            {name : '15',value: '15'},
                            {name : '30',value: '30'},
							{name : '60',value: '60'},
                            {name : '90',value: '90'},
							{name : '120',value: '120'},
                            {name : '150',value: '150'},
							{name : '180',value: '180'}
						  ]
              })
        }
		],

        buttons: [{
            text: 'Batal',
            handler: function() {
                this.up('form').getForm().reset();
                this.up('window').hide();
            }
        }, {
            text: 'Filter',
            handler: function() {
                var form    = formFilter.getForm();
                nosar   = form.findField('filter_nosar').value;
                vtdid   = form.findField('filter_nosar').displayTplData[0].id;
                dt1     = form.findField('d1').getValue().getFullYear()+'-'+(form.findField('d1').getValue().getMonth()+1)+'-'+form.findField('d1').getValue().getDate();
                //var dt1     = form.findField('d1').getValue().getMonth();
				t1      = form.findField('t1').getSubmitValue();
				minute1 = form.findField('kel_minute').value;
                
                storeGps.proxy.extraParams.nosar = nosar;
                storeGps.proxy.extraParams.dt1 = dt1;
                storeGps.proxy.extraParams.t1 = t1;
				storeGps.proxy.extraParams.minute1 = minute1;
				
                storeGps.load();

                gridGps.setTitle('STORY GPS ['+vtdid+']- '+nosar+' [' + form.findField('d1').getSubmitValue() + ' ' + t1+']');
                this.up('window').hide();
            }
        }]
    });

var winFilter;
function showFormFilter() {
    if (!winFilter) {
        winFilter = Ext.widget('window', {
            title: 'PERIODE STORY GPS',
            closeAction: 'hide',
            width: 400,
            //height: 300,
            layout: 'fit',
            resizable: false,
            modal: true,
            items: formFilter
        });
    }
    winFilter.show();
}

  

</script>

