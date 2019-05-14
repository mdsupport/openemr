/**
 * frame_proxies.js
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2016 Kevin Yeh <kevin.y@integralemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

var RTop = {
    set location(tgt)
    {
        if ((typeof(tgt) === "string") && 
            ((tgt.indexOf('demographics.php') == -1) || (tgt.indexOf('set_pid') === -1))) {
            // tgt is simple url string, not demographics OR not patient related
            navigateTab(tgt,"pat", function () {
                activateTabByName("pat",true);
            });
        } else {
            $('#mainModal').modal('hide');
            if (typeof(tgt) === "string") {
                // Split tgt url into url, pid, (enc)
                let orig = tgt;
                tgt = tgt.split("?set_pid=")[1];
                let arg = tgt.split("&set_encounterid=");
                tgt = {url: orig, pid: arg[0], enc: arg[1]};
            }
            navigateTab(tgt.url,"pat", function () {
                activateTabByName("pat",true);
            });
            // Get patient data
            // Assign handlers immediately after making the request,
            // and remember the jqXHR object for this request
            var jqxhr = $.ajax({
                url: 'main_title.php',
                data: {pid: tgt.pid}
              })
              .done(function(resp) {
                let objResp = $.parseJSON(resp);
                left_nav.setPatient(objResp.pt.name, objResp.pt.pid, objResp.pt.pubpid, '', objResp.pt.dob_age);
                app_view_model.application_data[attendant_type]().encounterArray.removeAll();
                let ix = 0;
                let cnt = objResp.enc.length;
                while (cnt--) {
                    let enc = objResp.enc[ix++];
                    app_view_model.application_data[attendant_type]().encounterArray.push(
                        new encounter_data(enc.encounter, enc.date, enc.cat));
                }
                app_view_model.application_data[attendant_type]().selectedEncounterID(objResp.enc[0].encounter);
                encurl = 'patient_file/encounter/encounter_top.php?set_encounter=' + objResp.enc[0].encounter + '&pid=' + tgt.pid;
                left_nav.loadFrame('enc2', 'enc', encurl);
                activateTabByName("pat",true);
              })
              .fail(function() {
                alert( "error" );
              });
        }
    }
};

var attendant_type = 'patient';

var left_nav = {

};

left_nav.setPatient = function(pname, pid, pubpid, frname, str_dob)
{
    if(
        (app_view_model.application_data.patient()!==null)
        && (pid===app_view_model.application_data.patient().pid()))
    {
        app_view_model.application_data.patient().pname(pname);
        app_view_model.application_data.patient().pubpid(pubpid);
        app_view_model.application_data.patient().str_dob(str_dob);

        return;
    }
    var new_patient=new patient_data_view_model(pname,pid,pubpid,str_dob);
    app_view_model.application_data.patient(new_patient);
    app_view_model.application_data.therapy_group(null)
    // Disable the following tab load if last encounter is loaded instead of history
    // navigateTab(webroot_url+"/interface/patient_file/history/encounters.php","enc", function () {
    //    tabCloseByName('rev');
    //});

    /* close therapy group tabs */
    tabCloseByName('gdg');
    attendant_type = 'patient';
    app_view_model.attendant_template_type('patient-data-template');
};

left_nav.setTherapyGroup = function(group_id, group_name){

    if(
        (app_view_model.application_data.therapy_group()!==null)
        && (group_id===app_view_model.application_data.therapy_group().gid()))
    {
        app_view_model.application_data.therapy_group().gname(group_name);
        app_view_model.application_data.therapy_group().gid(group_id);
        navigateTab(webroot_url+"/interface/therapy_groups/index.php?method=listGroups","gfn", function () {
            activateTabByName('gdg',true);
        });
        return;
    }
    var new_therapy_group=new therapy_group_view_model(group_id,group_name);
    app_view_model.application_data.therapy_group(new_therapy_group);
    app_view_model.application_data.patient(null);
    navigateTab(webroot_url+"/interface/therapy_groups/index.php?method=listGroups","gfn");
    navigateTab(webroot_url+"/interface/therapy_groups/index.php?method=groupDetails&group_id=from_session","gdg", function () {
        activateTabByName('gdg',true);
    });
    navigateTab(webroot_url+"/interface/patient_file/history/encounters.php","enc");
    tabCloseByName('gng');
    /* close patient tab */
    tabCloseByName('pat');
    attendant_type = 'therapy_group';
    app_view_model.attendant_template_type('therapy-group-template');
}

left_nav.setPatientEncounter = function(EncounterIdArray,EncounterDateArray,CalendarCategoryArray)
{

    app_view_model.application_data[attendant_type]().encounterArray.removeAll();
    for(var encIdx=0;encIdx<EncounterIdArray.length;encIdx++)
    {
        app_view_model.application_data[attendant_type]().encounterArray.push(
            new encounter_data(EncounterIdArray[encIdx]
                              ,EncounterDateArray[encIdx]
                              ,CalendarCategoryArray[encIdx]));
    }
}

left_nav.setEncounter=function(edate, eid, frname)
{
    app_view_model.application_data[attendant_type]().selectedEncounterID(eid);
}

left_nav.loadFrame=function(id,name,url)
{
    if(name==="")
    {
        name='enc';
    }
    navigateTab(webroot_url+"/interface/"+url,name, function () {
        activateTabByName(name,true);
    });
}

left_nav.syncRadios = function()
{

};

left_nav.clearEncounter = function () {
    app_view_model.application_data[attendant_type]().selectedEncounterID('');
};

//Removes an item from the Encounter drop down.
left_nav.removeOptionSelected = function (EncounterId)
{
    var self = app_view_model.application_data[attendant_type]();
    for (var encIdx = 0; encIdx < self.encounterArray().length; encIdx++) {
        var curEnc = self.encounterArray()[encIdx];
        if (curEnc.id() === self.selectedEncounterID()) {
            self.encounterArray.remove(curEnc);
            return;
        }
    }
};
