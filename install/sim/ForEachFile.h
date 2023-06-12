/*	This file is part of the auxiliaries library.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: ForEachFile.h,v 1.18 2020-08-14 16:56:47 dick Exp $
*/

#ifndef	_FOREACHFILE_H_
#define _FOREACHFILE_H_

#include	"fname.h"
#include	<sys/types.h>
#include	<sys/stat.h>

/****
* ForEachFile(const Fchar *Fn, int (*proc_before)(...), void(*proc_after)(...)):
  The tree from Fn is visited depth-first, and each file or directory met going
  down is passed to the routine proc_before() and on the way up to the routine
  proc_after(). One of proc_before and proc_after may be NULL. The two routines
  have identical parameter lists:

    int proc_before(const Fchar *Fn, const char *msg, const struct stat *fs):
	the file or directory Fn has been reached top-down;
	if msg != NULL, an error prevails the text of which is *msg, and
	in that case proc_after() will not be called for this item;
	otherwise fs points to the stat buffer for Fn.

	If proc_before() returns 0, the tree dangling from Fn is not further
	visited, unless it is the first argument of ForEachFile().
	If proc_before() always returns 1, all reachable files are reported;
	if it always returns 0, only one level of files and directories are
	reported. But finer control is possible.

    void proc_after(const Fchar *Fn, const char *msg, const struct stat *fs):
	the file or directory Fn has been reached bottom-up;
	if msg != NULL, an error prevails the text of which is *msg (this can
	only happen when proc_before == NULL);
	otherwise fs points to the stat buffer for Fn.

    The routines proc_before() and proc_after() are not called with the
    directory names "." or ".." unless it is the first argument to
    ForEachFile().

* MAX_PATH_NAME_LENGTH is the maximum length of the file name Fn, including
  directories.
****/

/* Public entries */
#define	MAX_PATH_NAME_LENGTH	300

extern void ForEachFile(
	const Fchar *Fname,
	int (*proc_before)(const Fchar *, const char *, const struct stat *),
	void (*proc_after)(const Fchar *, const char *, const struct stat *)
);

/* for avoiding awkward dir tests */
extern int is_dirstat(const struct stat *fs);
extern int is_Dirname(const Fchar *Fn);
extern int is_Admin_Dirname(const Fchar *Fn);	/* !=0 for "." and ".." */

#endif	/* _FOREACHFILE_H_ */
