# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## Digital Recruitment & Application Management Portal

**Project Type:** Web-Based Recruitment and Application Management System  
**Version:** 1.0  
**Status:** Initial Product Specification  
**Backend:** PHP — Laravel  
**Frontend:** Laravel Blade + Tailwind CSS  
**Database:** PostgreSQL

---

# 1. PROJECT OVERVIEW

## 1.1 Introduction

The Digital Recruitment & Application Management Portal is a web-based platform designed to digitize and simplify an organization's recruitment and application process.

The system will allow prospective applicants to view available positions, complete application forms, submit their applications online, and track the progress of their applications using a unique application reference number.

The organization will have an administrative dashboard where authorized personnel can manage recruitment positions, review submitted applications, filter applicants, update application statuses, communicate with applicants, and monitor the entire recruitment process.

The goal is to eliminate unnecessary paperwork, improve application management, reduce administrative workload, and provide a more transparent and efficient recruitment experience.

## 1.2 Primary Objectives

- Digitize the organization's application process.
- Provide a simple and user-friendly application experience.
- Allow applicants to submit applications online.
- Generate unique application reference numbers.
- Allow applicants to track their application progress.
- Provide administrators with an organized application management dashboard.
- Automate application notifications and status emails.
- Make applicant filtering, searching, and reviewing easier.
- Maintain a complete history of application activities.
- Make the system flexible enough to accommodate future recruitment exercises.

# 2. SYSTEM USERS AND ROLES

The system will have two primary user categories.

## 2.1 Applicants

Applicants are members of the public who want to apply for available positions.

Applicants should be able to:

- View available positions.
- Read position descriptions and requirements.
- Complete application forms.
- Submit applications.
- Receive unique application reference numbers.
- Receive email notifications.
- Track their applications.
- View application status and relevant instructions.

Applicants should NOT be required to create an account just to submit an application.

## 2.2 Administrators

Administrators are authorized organization personnel responsible for managing applications and recruitment activities.

Administrators should be able to:

- Access the administrative dashboard.
- View submitted applications.
- Create and manage recruitment positions.
- Review applicant information.
- Filter and search applications.
- Change application statuses.
- Shortlist applicants.
- Reject applications.
- Invite applicants for interviews.
- Send notifications.
- View application histories.
- Export application records.
- Manage email templates.
- Manage administrative users and permissions.

# 3. PUBLIC-FACING WEBSITE

## 3.1 Homepage

The public homepage should provide a clean and professional introduction to the recruitment portal.

It should include:

- Organization branding.
- Brief introduction.
- Available opportunities.
- Application instructions.
- Application deadline information where applicable.
- Apply Now button.
- Track Application button.

The interface should be responsive and optimized for mobile devices.

## 3.2 Available Positions

Applicants should be able to view positions currently accepting applications.

Each position should display:

- Position title.
- Department (if applicable).
- Short description.
- Requirements.
- Application opening date.
- Application closing date.
- Application status.

Each available position should have an Apply Now button.

Only published and currently open positions should accept applications.

Closed, unpublished, or expired positions must not accept new submissions.

# 4. APPLICATION FORM

## 4.1 General Requirements

The application form should use a multi-step interface rather than presenting every field on one long page.

The form should include:

- Clear section headings.
- Required-field indicators.
- Helpful field descriptions.
- Validation messages.
- Previous and Next navigation.
- Progress indicator.
- Mobile-friendly layouts.
- Review-before-submission functionality.

Required fields must be marked with an asterisk (*).

Optional fields must not prevent submission when left empty.

## 4.2 Section One: Position Selection

Fields:

- Position Applying For (Required).

The position may be preselected when an applicant clicks Apply Now from a specific position.

Applicants must only be able to apply for positions that are currently accepting applications.

## 4.3 Section Two: Personal Information / Biodata

Fields:

| Field | Type | Requirement |
|---|---|---|
| First Name | Text | Required |
| Middle Name | Text | Optional |
| Last Name | Text | Required |
| Email Address | Email | Required |
| Phone Number | Telephone | Required |
| Date of Birth | Date | Required |
| Gender/Sex | Select | Required |
| State of Origin | Select | Optional |
| Local Government Area | Select | Optional |
| Current State of Residence | Select | Required |
| Current LGA | Select | Optional |
| Residential Address | Textarea | Required |

Location functionality:

- Provide a list of Nigerian states and the Federal Capital Territory.
- Allow applicants to select their state.
- Dynamically populate available LGAs based on the selected state.
- Allow administrators to filter applications by state and LGA.

## 4.4 Section Three: Educational Qualifications

Applicants should select their highest educational qualification.

Qualification options:

- SSCE
- NCE
- ND
- HND
- BSc
- BA
- MSc
- MA
- PhD
- Other

Additional fields:

| Field | Type | Requirement |
|---|---|---|
| Highest Qualification | Select | Required |
| Institution Name | Text | Optional |
| Course of Study | Text | Optional |
| Graduation Year | Number/Select | Optional |
| Additional Qualification Details | Textarea | Optional |

The qualification list should be manageable and expandable in future.

## 4.5 Section Four: Skills and Competencies

Applicants should be able to provide their skills and competencies.

Requirements:

- Allow multiple skills.
- Provide an Add Skill button.
- Allow applicants to remove added skills.
- Prevent duplicate entries.
- Support free-text skill entry.

Example:

- Communication
- Leadership
- Microsoft Office
- Project Management
- Programming
- Administration

The system should store skills in a structured format to support administrative filtering.

## 4.6 Section Five: Leadership Experience

Question:

**Do you have any leadership experience?**

Options:

- Yes
- No

Conditional behavior:

If the applicant selects NO:
- Hide additional leadership fields.
- Allow the applicant to proceed.

If the applicant selects YES:
Display:

| Field | Type | Requirement |
|---|---|---|
| Organization/Institution | Text | Required |
| Leadership Position/Role | Text | Required |
| Duration | Text | Optional |
| Description of Experience | Textarea | Required |

The conditional fields should only be validated when YES is selected.

## 4.7 Section Six: About Yourself

Provide a large text area asking:

"Tell us about yourself."

This section allows applicants to introduce themselves, describe their experience, and explain why they are suitable for the position.

The field should support a configurable character limit.

## 4.8 Section Seven: Supporting Documents

The document upload section should support configurable document requirements.

Potential documents:

- Curriculum Vitae (CV).
- Passport Photograph.
- Educational Certificates.
- Other Supporting Documents.

Requirements:

- Allow PDF and approved image formats.
- Set configurable maximum file sizes.
- Validate file types and sizes.
- Allow administrators to configure required and optional documents.
- Store files securely.
- Prevent unauthorized public access to uploaded documents.

The exact mandatory documents will be determined from the organization's official application requirements.

## 4.9 Section Eight: Review and Submission

Before submission, applicants should see a summary of their information.

The review page should organize information into sections:

- Position Applied For.
- Personal Information.
- Educational Qualifications.
- Skills.
- Leadership Experience.
- About Yourself.
- Uploaded Documents.

Applicants should be able to return to previous sections and make corrections.

The final submission button should require confirmation.

After successful submission:

- Generate a unique application reference number.
- Save the application.
- Record the submission date and time.
- Send an application confirmation email.
- Display a successful submission screen.

# 5. APPLICATION REFERENCE NUMBER

Every successfully submitted application must receive a unique reference number.

Example:

APP-2026-8F42K

Requirements:

- Reference numbers must be unique.
- They should be generated automatically.
- They must be associated with the application record.
- They should be displayed immediately after submission.
- They should be included in confirmation emails.
- They should be searchable in the administrative dashboard.

Applicants should be encouraged to keep their reference numbers safe.

# 6. APPLICATION TRACKING PORTAL

A separate public tracking page should be available.

## 6.1 Tracking Interface

Fields:

- Application Reference Number.
- Email Address.

Button:

Track Application.

The combination of reference number and email address must be verified before displaying application information.

The system must avoid revealing whether an unrelated applicant's reference number exists.

## 6.2 Tracking Results

After successful verification, applicants should see:

- Application reference number.
- Position applied for.
- Submission date.
- Current application status.
- Application progress timeline.
- Relevant instructions or updates.

## 6.3 Application Status Timeline

Example:

1. Application Submitted.
2. Under Review.
3. Shortlisted.
4. Interview.
5. Final Review.
6. Selected.

Alternative outcomes:

- Rejected.
- More Information Required.
- Withdrawn.

Not every application must pass through every stage.

The timeline should show actual status transitions rather than assuming every stage has been completed.

## 6.4 Privacy

Applicants must only see information relating to their own applications.

Internal administrator notes, reviewer comments, and confidential recruitment information must never appear on the public tracking page.

# 7. ADMINISTRATIVE DASHBOARD

The administrative dashboard is the central management interface.

It should be clean, professional, responsive, and easy for non-technical organization personnel to use.

## 7.1 Dashboard Overview

Display summary statistics:

- Total Applications.
- New Applications.
- Under Review.
- Shortlisted.
- Interview.
- Selected.
- Rejected.

Statistics should be filterable by recruitment exercise or position where appropriate.

## 7.2 Application Management

Provide a central applications page.

Display applications in a searchable and filterable table.

Suggested columns:

| Column | Description |
|---|---|
| Reference Number | Unique application ID |
| Applicant Name | Full name |
| Email | Applicant email |
| Phone | Contact number |
| Position | Position applied for |
| State | Current state of residence |
| Qualification | Highest qualification |
| Status | Current application status |
| Date Submitted | Submission date |
| Actions | View application |

Filtering options:

- Application status.
- Position.
- State.
- LGA.
- Educational qualification.
- Gender.
- Submission date.
- Leadership experience.

Search should support:

- Applicant name.
- Email.
- Phone number.
- Application reference number.

Provide pagination and sorting.

## 7.3 Application Details

When an administrator opens an application, display a complete applicant profile.

Organize it into sections:

1. Application Overview.
2. Personal Information.
3. Contact Information.
4. Location.
5. Educational Qualifications.
6. Skills and Competencies.
7. Leadership Experience.
8. Personal Statement.
9. Supporting Documents.
10. Application Activity History.

Administrators should be able to view and securely download uploaded documents.

## 7.4 Application Status Management

Administrators should be able to change application statuses.

Initial status options:

- Submitted.
- Under Review.
- Shortlisted.
- Interview.
- Final Review.
- Selected.
- Rejected.
- More Information Required.
- Withdrawn.

Status changes should:

- Require appropriate authorization.
- Record the administrator responsible.
- Record the previous status.
- Record the new status.
- Record the date and time.
- Optionally include an internal note.
- Trigger the appropriate email notification when configured.

The system should maintain a complete status history.

## 7.5 Internal Notes

Administrators should be able to add private notes to applications.

Notes should:

- Be visible only to authorized administrators.
- Record the author.
- Record creation date and time.
- Never appear on the applicant tracking portal.
- Never be included in public applicant emails.

## 7.6 Application Export

Administrators should be able to export application data.

Supported formats:

- CSV.
- Excel.

Export functionality should respect active filters and permissions.

# 8. RECRUITMENT POSITION MANAGEMENT

Administrators should be able to create and manage available positions.

Position fields:

- Position Title.
- Department.
- Description.
- Requirements.
- Opening Date.
- Closing Date.
- Application Status.
- Required Documents.
- Optional Documents.

Position statuses:

- Draft.
- Published.
- Closed.
- Archived.

Requirements:

- Create new positions.
- Edit existing positions.
- Publish positions.
- Close applications.
- Archive old recruitment exercises.
- View applications associated with each position.
- Configure required and optional documents.

Closing a position must prevent new applications without deleting existing records.

# 9. AUTOMATED EMAIL NOTIFICATIONS

The system should support automated transactional emails.

Emails should be triggered by application events and configured status transitions.

## 9.1 Application Received

Trigger: Successful application submission.

Subject: Application Received — [Organization Name]

Include:

- Applicant name.
- Position applied for.
- Application reference number.
- Submission date.
- Tracking instructions.

## 9.2 Application Under Review

Trigger: Application status changes to Under Review.

Notify applicants that their application is being reviewed.

## 9.3 Shortlisting Notification

Trigger: Application status changes to Shortlisted.

Notify the applicant that they have been shortlisted.

## 9.4 Interview Invitation

Trigger: Application status changes to Interview.

Where applicable, include:

- Interview date.
- Interview time.
- Interview venue or meeting link.
- Interview instructions.
- Contact information.

Interview information should be entered and managed by authorized administrators.

## 9.5 Selection Notification

Trigger: Application status changes to Selected.

Send a congratulatory notification with any relevant next steps.

## 9.6 Rejection Notification

Trigger: Application status changes to Rejected.

Send a professional and respectful rejection notification.

## 9.7 Additional Information Request

Trigger: Application status changes to More Information Required.

Send the applicant the required instructions.

## 9.8 Email Management

Administrators should be able to:

- View available email templates.
- Edit email subject lines.
- Edit email content.
- Use dynamic placeholders.
- Enable or disable automatic notifications.
- Preview emails before saving.

Supported placeholders:

- {{applicant_name}}
- {{application_reference}}
- {{position_title}}
- {{application_status}}
- {{organization_name}}

## 9.9 Email Delivery Architecture

Use Laravel Mail and Laravel Queues.

Emails should be processed in the background rather than delaying application submissions or dashboard operations.

The system should:

- Support SMTP-compatible email providers.
- Support retries for temporary delivery failures.
- Record email delivery attempts and errors.
- Avoid sending duplicate notifications for the same status event.
- Provide administrators with basic email delivery logs.

A reliable transactional email provider should be selected during deployment.

# 10. ADMINISTRATOR AND PERMISSION MANAGEMENT

The system should support secure administrator authentication.

No public administrator registration.

Administrators must be created or invited by an authorized administrator.

Initial roles:

**Super Administrator**
- Full system access.
- Manage administrator accounts.
- Manage positions.
- View and manage all applications.
- Manage settings.
- Manage email templates.

**Recruitment Administrator**
- View applications.
- Review applicant information.
- Update application statuses.
- Shortlist applicants.
- Manage recruitment exercises where authorized.

**Application Reviewer**
- View assigned applications.
- Review applicant information.
- Add internal notes.
- Submit recommendations.
- Cannot manage system-wide settings or administrator accounts.

Permissions should be configurable and enforced on the backend.

# 11. APPLICATION ACTIVITY AND AUDIT LOGS

The system should record important activities.

Examples:

- Application submitted.
- Application status changed.
- Application reviewed.
- Internal note added.
- Applicant shortlisted.
- Interview invitation sent.
- Application rejected.
- Position created or updated.
- Administrator account created.

Each activity should record:

- Activity type.
- Related application or record.
- User responsible, where applicable.
- Timestamp.
- Relevant details.

Audit records should not be editable through ordinary administrative operations.

# 12. USER EXPERIENCE AND INTERFACE REQUIREMENTS

## 12.1 Applicant Experience

The public-facing experience must prioritize simplicity.

Requirements:

- Mobile-first design.
- Clear instructions.
- Minimal unnecessary fields.
- Multi-step form.
- Progress indicator.
- Helpful validation.
- Clear required/optional indicators.
- Accessible typography.
- Easy document uploads.
- Review-before-submission.
- Clear success confirmation.

Applicants should not need technical knowledge to complete the process.

## 12.2 Administrator Experience

The administrative interface should prioritize productivity.

Requirements:

- Clean dashboard.
- Easy navigation.
- Search and filtering.
- Clear status indicators.
- Organized application details.
- Bulk selection for supported actions.
- Responsive tables.
- Pagination.
- Confirmation before destructive actions.
- Clear success and error messages.

Avoid unnecessary interface complexity.

# 13. SECURITY REQUIREMENTS

The system will handle personal information and confidential applicant documents.

Security is a major requirement.

Implement:

- Secure administrator authentication.
- Role-based access control.
- CSRF protection.
- Server-side input validation.
- Output escaping.
- Secure password hashing.
- Rate limiting on public endpoints.
- Secure document upload validation.
- Private document storage.
- Authorization checks on every protected resource.
- Protection against unauthorized application access.
- Secure email configuration.
- Database backups.
- Audit logging.
- HTTPS in production.

Application tracking must require both the reference number and applicant email.

Never expose internal notes, uploaded documents, or sensitive applicant information through public URLs.

The organization should establish appropriate data retention, consent, and privacy policies in line with applicable Nigerian data protection requirements, including the Nigeria Data Protection Act.

# 14. TECHNICAL ARCHITECTURE

## 14.1 Technology Stack

Backend:
- PHP 8.3+.
- Laravel.

Frontend:
- Laravel Blade.
- Tailwind CSS.
- Lightweight JavaScript where necessary.

Database:
- PostgreSQL.

Authentication:
- Laravel authentication.

Authorization:
- Laravel Policies/Gates and role-based permissions.

Email:
- Laravel Mail.
- SMTP-compatible transactional email provider.

Background Processing:
- Laravel Queues.
- Database queue initially, with Redis as an optional future improvement.

File Storage:
- Laravel Filesystem.
- Private local storage initially.
- S3-compatible storage when needed.

Deployment:
- VPS or compatible Laravel hosting environment.

## 14.2 Architecture Philosophy

Build a modular monolithic Laravel application.

Do not introduce unnecessary complexity.

Avoid:

- Microservices.
- Separate frontend and backend applications.
- Unnecessary APIs.
- Multiple databases.
- Complex infrastructure.
- Premature optimization.

The system should be easy to deploy, maintain, modify, and scale.

## 14.3 Suggested Application Modules

Organize the application into logical modules:

1. Public Website.
2. Recruitment Positions.
3. Applicant Management.
4. Application Submission.
5. Application Tracking.
6. Education and Skills.
7. Document Management.
8. Application Workflow.
9. Email Notifications.
10. Administrator Management.
11. Reports and Exports.
12. Audit Logs.
13. System Settings.

# 15. INITIAL DATABASE ENTITIES

The following are the proposed core database entities:

- users
- roles
- permissions
- positions
- applications
- applicant_profiles
- qualifications
- application_qualifications
- skills
- application_skills
- leadership_experiences
- application_documents
- application_statuses
- application_status_histories
- application_notes
- email_templates
- email_logs
- notifications
- audit_logs
- states
- local_governments
- recruitment_exercises

The final schema should be designed around the actual application form and recruitment workflow.

Avoid duplicating applicant data unnecessarily.

Applications must retain their submitted information even if an administrator later edits a position or recruitment configuration.

# 16. REPORTING AND ANALYTICS

Provide basic recruitment statistics.

Reports should include:

- Total applications.
- Applications per position.
- Applications by status.
- Applications by state.
- Applications by qualification.
- Applications by gender.
- Applications received within a date range.
- Shortlisted applicants.
- Selected applicants.
- Rejected applicants.

Provide CSV and Excel export functionality.

# 17. NON-FUNCTIONAL REQUIREMENTS

Performance:
- Application pages should load efficiently.
- Use pagination for large datasets.
- Queue email notifications.
- Optimize database queries.

Scalability:
- Support increasing application volumes.
- Keep modules maintainable.
- Use appropriate database indexes.
- Support external storage when necessary.

Reliability:
- Prevent duplicate application submissions.
- Use database transactions during submission.
- Maintain reliable background job processing.
- Provide backup and recovery procedures.

Maintainability:
- Follow Laravel conventions.
- Use reusable components.
- Avoid unnecessarily large controllers.
- Keep business logic organized.
- Use clear naming conventions.
- Write automated tests for critical workflows.

Accessibility:
- Clear labels.
- Keyboard-friendly navigation.
- Appropriate contrast.
- Accessible validation messages.

# 18. MVP SCOPE

The first release should focus on the essential recruitment workflow.

Must-have features:

1. Public homepage.
2. Available positions.
3. Multi-step application form.
4. Required and optional fields.
5. Conditional leadership experience fields.
6. Nigerian state and LGA selection.
7. Skills management.
8. Supporting document uploads.
9. Application submission.
10. Unique application reference generation.
11. Email confirmation.
12. Public application tracking.
13. Administrator authentication.
14. Administrative dashboard.
15. Application listing and filtering.
16. Application detail view.
17. Application status management.
18. Automated status emails.
19. Internal notes.
20. Basic application exports.
21. Recruitment position management.
22. Application history.

# 19. FUTURE ENHANCEMENTS

These features are not required for the initial release but should be considered in future versions:

- Applicant accounts.
- Multiple recruitment exercises.
- Advanced recruitment analytics.
- Interview scheduling.
- Interview evaluation forms.
- Multiple reviewer assignments.
- Bulk status updates.
- SMS notifications.
- WhatsApp notifications.
- Applicant document resubmission.
- Configurable application questions.
- Custom recruitment workflows.
- Advanced email delivery analytics.
- API integrations.
- Public recruitment announcements.

# 20. DEVELOPMENT PHASES

The project should be developed incrementally.

**Phase 1 — Requirements and Design**
- Review the official sample application form.
- Confirm mandatory and optional fields.
- Confirm recruitment stages.
- Confirm required documents.
- Finalize applicant and administrator workflows.
- Approve UI/UX designs.

**Phase 2 — Foundation**
- Create Laravel project.
- Configure PostgreSQL.
- Set up authentication.
- Configure administrator roles and permissions.
- Establish application structure.

**Phase 3 — Public Portal**
- Homepage.
- Position listings.
- Position details.
- Multi-step application form.
- Conditional fields.
- Form validation.
- Document uploads.

**Phase 4 — Application Processing**
- Application submission.
- Unique reference generation.
- Application storage.
- Duplicate submission protection.
- Submission confirmation.

**Phase 5 — Tracking**
- Public tracking page.
- Reference and email verification.
- Application status timeline.

**Phase 6 — Admin Dashboard**
- Dashboard statistics.
- Application listing.
- Search and filtering.
- Application details.
- Internal notes.
- Status management.

**Phase 7 — Email Automation**
- Email provider integration.
- Queue configuration.
- Email templates.
- Automatic status notifications.
- Email delivery logs.

**Phase 8 — Reports and Management**
- Recruitment position management.
- Application exports.
- Reports.
- Administrator permissions.
- Audit logs.

**Phase 9 — Testing and Deployment**
- Functional testing.
- Security testing.
- Mobile responsiveness.
- Email delivery testing.
- Production deployment.
- Backup configuration.
- Final user acceptance testing.

# 21. ACCEPTANCE CRITERIA

The system will be considered ready for its initial production release when:

1. Applicants can view available recruitment positions.
2. Applicants can complete and submit the application form.
3. Required fields are validated correctly.
4. Optional fields can be skipped.
5. Conditional fields behave correctly.
6. Applicants receive unique reference numbers.
7. Confirmation emails are delivered or properly queued.
8. Applicants can securely track their applications.
9. Administrators can view and search applications.
10. Administrators can filter applications by relevant attributes.
11. Administrators can securely view applicant information and documents.
12. Administrators can update application statuses.
13. Status changes are recorded in application history.
14. Appropriate email notifications are triggered.
15. Administrators can create and manage positions.
16. Application exports work correctly.
17. Unauthorized users cannot access protected information.
18. The portal works properly on mobile and desktop devices.

# 22. FINAL DEVELOPMENT PRINCIPLE

The primary objective is to build a professional, reliable, easy-to-use recruitment management system without unnecessary complexity.

The system must serve two equally important groups:

**Applicants:** A simple, transparent, mobile-friendly application experience.

**Organization Personnel:** An organized administrative system that makes recruitment, reviewing, filtering, communication, and decision-making easier.

Build the system as a maintainable Laravel monolith.

Prioritize functionality, simplicity, vsecurity, and user experience over unnecessary technical complexity.

Do not implement future enhancements before the core recruitment workflow is complete.

**END OF PRODUCT REQUIREMENTS DOCUMENT**