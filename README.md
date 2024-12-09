# auth/disguise: User Disguises 

This Moodle authentication plugin allows the use of user disguises in certain Moodle contexts.

## Status Overview

TODO: Update this section.

As at this commit, it implements basic proof-of-concept functionality for forum course module activity contexts only.
The intention is for this to be initially compatible with, and expand to, course contexts and site contexts as well. 

Initially, using existing core hooks, course module context configuration will be built into this plugin. 
As core hooks are not available for course context configuration (without using the Course Custom Fields API), the initial proof-of-concept development may leverage configuration and custom SQL to demonstrate this functionality working without changes to core, until it can be merged into the same approach as course module configuration along with the implementation of Moodle core hook addition via an appropriate tracker.

- 30/Jun/2023:

Git patch diff `0001-MDL-76126-course-Add-four-callbacks-for-course-forms.patch` adds four core hooks similar to the coursemodule_ hooks from MDL-52534, but depends on integration into core. Without this, implementation must rely on custom course fields (config) with specific configuration to match SQL checks to extract data from core. The subsequent commit includes this note and functionality which depends upon it.

## (Proof-of-Concept) Manual Configuration / Testing / Demo

TODO: Update/remove this section.

Interm configuration for exploring/improving and demonstrating the development functionality requires the following steps:

1. Add a course custom field `disguises_mode`

- Go to /course/customfield.php
- Click "Add a new category"
- Click the pen icon to rename "Other fields" to "User Disguises"
- Click "Add a new custom field" and select "Dropdown menu"
- Set Name: User Disguises Mode (Course)
- Set Shortname: disguises_mode
- Set Description: This mode determines if user disguises are in use for this course and/or its course module activities.
- Set Required: Yes
- Set Options: (as per the lang strings defined for future development)

Disabled - No user disguises permitted in course or any activity.
Optional - Disguises may be used in course, or set by activity.
Modules only - Disguise may only be used where set by activity.
Everywhere - User disguises applied to course and all activities.

- Set Default value: Disabled - No user disguises permitted in course or any activity.
- Set Visible to: Teachers
- Save changes.

1. Enable User Disguies auth plugin

- Go to Site administration > Plugins > Authentication > Manage authentication
- On the User Disguises plugin, click the "Enable" eye icon.

2. Enable User Disguises site-wide

- Go to Site administration > Plugins > Authentication > Manage authentication
- For the User disguises plugin, click the "Settings" link.
- Set the auth_disguise | feature_status_site option to "Enabled"
- Save changes

## Testing / Demonstration

1. Create a course with `disguises_mode` enabled for it's modules

- Go to /course/management.php
- Click "Create new course"
- Set Course full name to your choice.
- Set Course short name to your choice.
- Set Course start date to your choice.
- Go to the "User Disguises" section.
- Set User Disguises Mode (Course): Modules only - Disguise may only be used where set by activity.
- Click "Save and display".

Test:

- Click Settings to go to Course settings.
- Confirm User Disguises Mode (Course) is set to Modules only.

2. Enable `disguises_mode` for the Announcements Forum

- Go to the course you created.
- Click on the Announcements forum activity.
- Click on Settings.
- Go to the "User Disguises" section.
- Set User Disguises Mode (Activity): Instructor-Safe - Students in this activity appear disguised to students/instructors.
- Click "Save and display"

Expected results:

- Site Administrations will see a DEBUG message advising they will not be redirected.
- Non-Site administrators will be redirected to a prompt page.)


## Installation

TODO: Add installation documentation.

### Git

TODO: Complete this section.

```
git clone xxx
```

### Download the zip

TODO: Complete this section.

## More information

TODO: Complete this section.

## License

TODO: Complete this section.

