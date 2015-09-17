# RB Agency Casting
Allows Casting Agents to post and manage jobs.

### Current Version 0.2.0


## Change Log

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

