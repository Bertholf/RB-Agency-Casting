# RB Agency Casting
Allows Casting Agents to post and manage jobs.

### Current Version 0.2.1

## Installation

Here are some guidelines to install the RB Agency Casting plugin:

1. Download the zip file here in github.
2. Unzip the file.
3. Rename the folder to "rb-agency-casting"
4. Login to your ftp.
5. Locate the /wp-content/plugins directory.
6. Upload the entire "rb-agency-casting" folder
7. Login to your website.
8. Go to Plugins > Inactive
9. Locate the "RB Agency Casting" plugin.
10. Click "Activate".

## Configuration

1. Login to your website.
2. Go to RB Agency » Settings » Interactive Settings. 
3. Under "Interactive Settings" click the "Settings" button. Here you may choose the settings and edit the agency's login and registration forms.
4. Click "Save Changes" button to update.

More detailed information:
http://rbplugin.com/plugin/rb-agency-casting/documentation/

If you would like help on installation and configuration, you may contact any of our support team:
http://rbplugin.com/contact-us/request-support/


## Change Log

### 0.2.1
* new feature - added warning when doing bulk delete in media & links section
* fixed - when admin rename the files in media & link section, the name of the file is not updating in job auditions section.
* fixed - jobs added are not automatically deleted when admin deletes job via backend
* fixed - when logged in as admin - browse more jobs takes you to application page instead of the job page
* new feature - when posting new job - added calendar select when adding date.
* new feature - when posting new job - added time select when adding time.
* new feature - created an "Agency/Producer" search filter in admin dashboard for casting job section.
* fixed - search by title filter is not working in admin casting job section
* fixed - invited users can still apply to the job that they already got invited to.
* fixed - profile added just added to the job automatically have audio file because it's pulling the profile's voice demo file instead of the audition file.
* fixed - custom field added and set to appear in Casting job is not showing.
* fixed - when non-logged in users apply for a job, user is redirected to login page which is correct, but after login, user must be redirected to job applying for.
* new feature - instead of location, jobs are filter now by region.
* fixed - job setting is invite only but model/talent users should can see jobs they are not invited
* fixed - admin cannot add profiles from admin shortlist to client casting cart
* fixed - when user clicks the View Details button in /browse-jobs/  user is taken back to /profile-member/ instead to the job detail.
* fixed - approved model/talent user cannot access job listing page
* fixed - name is not showing in job invite if profile has no photo
* new feature - add profiles in jobs even without primary photo (useful especially for agencies that allocate voiceover only)
* new feature - mp3 audition files and job applied are now listed in profile's dashboard
* new feature - created a new page in the admin dashboard to track down all auditions
* fixed - pending for approval casting agents can view their dashboard page instead of just seeing the pending for approval screen
* new feature - auditions bulk exporter

### 0.2.0
* ability to create additional custom fields for job posting form & casting registration
* ability to print/export the job details in a Booking Sheet (xls file)
* fixed - Approved clients screen shows the clients even after they are approved.
* creates default registration widget for casting agent’s login page
* ability to set specific models as private so only casting agents who are logged in can see the model.
* added thumbnails to casting cart email in send profile function. link each name and thumbnail to profile view.
* fixed - added 2 profiles in casting cart but did not show up, test by logging in as casting agent user.
* created a setting in the configuration section so admin can replace the icon to whatever icon they want by adding the image url of the icon.
* fixed the add to casting cart, add to job, remove from cart, remove from job logic. previously, if removed from cart, profiles gets removed from job also.
* fixed the ability to add profile in multiple jobs
* added a setting so admin can set where to redirect casting agents after logging in
* added a setting so admin can set if casting agents must be approved first before they can post jobs, view applicants, etc.
* fixed - logged in as demo-agent, the job created by casting agent is not showing if there are no applicants yet.
* added a calendar icon to the date field for Start Date in /browse-jobs/
* fixed - name and photo of the model/talent applicant are not showing in View Applicants page
* fixed - deleted profiles are not getting deleted in front-end, so it appears as there are still profiles when there is none actually.
* fixed - whether the user is inactive or pending for approval, the user should not be able to Browse Jobs and Apply.
* fixed - while logged in as casting agent, I can no longer view all jobs I created
* fixed - while logged in as casting agent, it shows I have no applicants and no job created, eventhough there is one job and one applicant when I view as admin
* fixed - admin approved demo-model, but when I logged in as demo-model, user cannot browser jobs
* fixed - filtering job is deleting the other job listed even after removing criteria
* fixed - html codes in email notifications
* added "Job Time Start" and "Job Time End" on casting jobs (admin and client view)
* fixed - if a profile is added by admin in the Talents Shortlisted by Admin section or in the Client's Casting Cart the profile should not get automatically be emailed of the job, unless the admin clicks on the “Resend notifcation to selected shortlisted talents” checkbox
* fixed - when browsing profiles added in jobs, the Add to Favorites button is missing.

### 0.1.9
* fixed - disabled “show casting cart” and “show favorites” settings but still showing
* fixed - new jobs getting created but no showing in job listing
* added bulk delete in manage castings section
* fixed - dropdown time format in creating new job is showing random letters
* created a redirect settings for logged in casting agents so admin have the ability to remove access of casting agents to their dashboard.
* fixed - casting agents can access dashboard and model list even if status is still pending for approval.

