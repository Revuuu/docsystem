import Sidebar from './modules/sidebar';
import Dashboard from './modules/dashboard';

import Signature from './modules/signature';
import SignatureDraw from './modules/signature-draw';

import Approver from './modules/approver';

import DocumentSearch from './modules/document-search';
import UserSearch from './modules/user-search';

import PdfViewer from './modules/pdf-viewer';

import UploadModal from './modules/upload-modal';
import RejectModal from './modules/reject-modal';

import UserProfile from './modules/user-profile';

import MoreMenu from './modules/more-menu';
import { initRoleAssignmentForm } from './modules/role-assignment';

import Auth from './modules/auth';
import PasswordModal from './modules/password-modal';
import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    Auth.init();

    Sidebar.init();

    Dashboard.init();

    Signature.init();

    SignatureDraw.init();

    Approver.init();

    DocumentSearch.init();

    UserSearch.init();
    
    PdfViewer.init();

    UploadModal.init();

    RejectModal.init();

    UserProfile.init();

    MoreMenu.init();

    PasswordModal.init();

    initRoleAssignmentForm();
});