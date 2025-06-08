# **🧪 COMMENT FUNCTIONALITY TESTING CHECKLIST**

## **Pre-Testing Setup**

### **Environment Preparation**
- [ ] WordPress & WooCommerce versions compatible
- [ ] Plugin activated without errors
- [ ] Test data available:
  - [ ] Project Manager role user
  - [ ] Customer role user  
  - [ ] Team Member role user
  - [ ] Test projects in different statuses

### **Settings Verification**
- [ ] Navigate to `WP Admin > Arsol Projects > Settings`
- [ ] Verify comment settings section exists:
  - [ ] "Enable Project Comments" toggle
  - [ ] "Enable Request Comments" toggle
  - [ ] "Enable Proposal Comments" toggle
  - [ ] "Comment Roles" multi-select
  - [ ] "Require Comment Moderation" checkbox
  - [ ] "Enable Comment Notifications" checkbox

---

## **1. Comment Permissions & Workflow Testing**

### **Project Manager Permissions**
- [ ] **Can moderate comments on ALL projects**
  - [ ] Login as project manager
  - [ ] Navigate to any project (not owned by them)
  - [ ] Verify they can see comment moderation options
  - [ ] Test approving/rejecting comments

- [ ] **Get notifications only on their own projects**
  - [ ] Create comment on project manager's own project
  - [ ] Verify notification email received
  - [ ] Create comment on another manager's project
  - [ ] Verify NO notification email received

### **Team Members & Customers Equal Permissions**
- [ ] **Both can comment when enabled**
  - [ ] Login as team member
  - [ ] Verify comment form visible on enabled post types
  - [ ] Submit comment successfully
  - [ ] Login as customer
  - [ ] Verify same comment capabilities

- [ ] **Both CANNOT moderate comments**
  - [ ] Verify no moderation controls visible
  - [ ] Verify cannot edit others' comments

### **WordPress CPT Edit Page**
- [ ] **Standard comment controls work**
  - [ ] Login as admin
  - [ ] Edit a project in WP Admin
  - [ ] Find "Discussion" meta box
  - [ ] Disable "Allow comments"
  - [ ] Save and verify comments disabled on frontend
  - [ ] Re-enable and verify comments work again

---

## **2. Notification System Testing**

### **Email Templates & Recipients**
- [ ] **WooCommerce-style emails sent**
  - [ ] Submit comment and check email formatting
  - [ ] Verify sender shows site name
  - [ ] Verify proper email headers

- [ ] **Correct recipients receive notifications**
  - [ ] Project Manager (if own project): ✅
  - [ ] Project Manager (if other's project): ❌
  - [ ] Customer/Team Member (if their content): ✅
  - [ ] Admin email (always): ✅

- [ ] **No dashboard notifications**
  - [ ] Verify WordPress admin bell/notification area empty
  - [ ] Check no custom notification widgets added

### **Internal vs Public Comment Notifications**
- [ ] **Internal comments - limited recipients**
  - [ ] Post internal comment
  - [ ] Verify only project team receives email
  - [ ] Verify customers don't receive internal comment emails

- [ ] **Public comments - all recipients**
  - [ ] Post public comment
  - [ ] Verify all authorized users receive email

---

## **3. Comment Display & Features Testing**

### **Threading**
- [ ] **Comments display threaded**
  - [ ] Post parent comment
  - [ ] Reply to comment
  - [ ] Verify reply indented/nested properly
  - [ ] Test multiple reply levels

### **No Comment Counts in Listings**
- [ ] **Project listings show no comment counts**
  - [ ] Navigate to project list pages
  - [ ] Verify no "(3 comments)" or similar text
  - [ ] Check both table and card layouts

### **Internal vs Client-Facing Comments**
- [ ] **Internal comment marking**
  - [ ] Login as project manager
  - [ ] Verify "Mark as internal" checkbox appears
  - [ ] Submit internal comment
  - [ ] Verify comment shows with 🔒 Internal badge

- [ ] **Comment visibility filtering**
  - [ ] Login as customer
  - [ ] Verify internal comments NOT visible
  - [ ] Login as project team member
  - [ ] Verify internal comments ARE visible

---

## **4. Integration with Existing Features**

### **No Workflow Triggers**
- [ ] **Comments don't affect project status**
  - [ ] Submit comment on active project
  - [ ] Verify project status unchanged
  - [ ] Check no workflow emails sent

### **No Reports/Exports**
- [ ] **Comments excluded from reports**
  - [ ] Generate project reports
  - [ ] Verify no comment data included
  - [ ] Check export files don't contain comments

### **No Activity Logging**
- [ ] **Comment activity not tracked**
  - [ ] Submit comment
  - [ ] Check activity logs/history
  - [ ] Verify commenting not recorded

---

## **5. Advanced Features (Verification of Exclusions)**

### **Confirm NO File Attachments**
- [ ] **Comment form has no file upload**
  - [ ] Check comment form fields
  - [ ] Verify no "Attach file" options

### **Confirm NO Comment Templates**
- [ ] **No quick reply templates**
  - [ ] Check admin areas
  - [ ] Verify no template management options

### **Confirm NO External Integration**
- [ ] **No Slack/Teams integration**
  - [ ] Submit comment
  - [ ] Verify no external notifications sent

---

## **6. User Experience Testing**

### **Comment Form Experience**
- [ ] **Form renders properly**
  - [ ] Check all three post types (projects, requests, proposals)
  - [ ] Verify form styling matches WooCommerce account area
  - [ ] Test responsive design on mobile

- [ ] **Comment submission flow**
  - [ ] Submit comment
  - [ ] Verify redirect stays in WooCommerce account area
  - [ ] Check success message appears
  - [ ] Verify comment appears (if approved)

### **Comment Display**
- [ ] **Comments integrate well**
  - [ ] Comments section visually separate from content
  - [ ] Styling consistent with plugin design
  - [ ] Internal comments clearly distinguished

---

## **7. Security Testing**

### **Permission Enforcement**
- [ ] **Unauthorized users blocked**
  - [ ] Logout completely
  - [ ] Verify comment form not visible
  - [ ] Try direct comment submission (should fail)

- [ ] **Role restrictions work**
  - [ ] Login as restricted role
  - [ ] Verify cannot access comment areas

### **Content Filtering**
- [ ] **XSS Protection**
  - [ ] Submit comment with `<script>` tags
  - [ ] Verify content sanitized
  - [ ] Check no JavaScript executes

---

## **8. Performance Testing**

### **Load Testing**
- [ ] **Page performance**
  - [ ] Load project page with 50+ comments
  - [ ] Verify reasonable load time
  - [ ] Check memory usage

- [ ] **Comment Threading Performance**
  - [ ] Create deeply nested comment threads
  - [ ] Verify display remains responsive

---

## **9. Error Handling Testing**

### **Network Issues**
- [ ] **Comment submission failures**
  - [ ] Simulate network timeout during submission
  - [ ] Verify user-friendly error message
  - [ ] Test retry functionality

### **Invalid Data**
- [ ] **Form validation**
  - [ ] Submit empty comment
  - [ ] Submit overly long comment
  - [ ] Verify proper validation messages

---

## **10. Browser Compatibility**

### **Cross-Browser Testing**
- [ ] **Chrome** - Full functionality test
- [ ] **Firefox** - Full functionality test  
- [ ] **Safari** - Full functionality test
- [ ] **Edge** - Full functionality test

### **Mobile Testing**
- [ ] **iOS Safari** - Comment form and display
- [ ] **Android Chrome** - Comment form and display

---

## **🚨 CRITICAL FAILURE POINTS**

**Immediate Fix Required If:**
- [ ] Comments visible to unauthorized users
- [ ] Internal comments visible to customers
- [ ] Project managers can't moderate comments
- [ ] Comments trigger workflow changes
- [ ] Site crashes when comments enabled
- [ ] XSS vulnerabilities found

---

## **✅ SIGN-OFF CHECKLIST**

**Before Production Deployment:**
- [ ] All critical tests passed
- [ ] No security vulnerabilities
- [ ] Performance acceptable
- [ ] Documentation updated
- [ ] Backup created
- [ ] Rollback plan ready

**Stakeholder Approval:**
- [ ] Project Manager approved functionality
- [ ] Customer experience approved
- [ ] Technical review completed
- [ ] Security review completed

---

## **📋 TEST RESULTS LOG**

| Test Category | Status | Notes | Tester | Date |
|--------------|--------|-------|--------|------|
| Permissions  |        |       |        |      |
| Notifications|        |       |        |      |
| Display      |        |       |        |      |
| Integration  |        |       |        |      |
| Security     |        |       |        |      |
| Performance  |        |       |        |      |

---

**📝 NOTES:**
- Test in staging environment first
- Use real user accounts, not test accounts
- Document any deviations from expected behavior
- Include screenshots for visual issues
- Test with real project data, not Lorem Ipsum 