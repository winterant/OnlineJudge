/*	This file is part of the auxiliaries library.
	Written by Dick Grune, VU, Amsterdam; dick@dickgrune.com
	$Id: ForEachFile.c,v 1.29 2020-08-14 16:56:47 dick Exp $
*/

#include	<string.h>
#include	<sys/types.h>
#include	<sys/stat.h>
#include	<dirent.h>
#include	<errno.h>

#include	"ForEachFile.h"

/* Library module source prelude */
#undef	_FOREACHFILE_CODE_
#ifndef	lint
#define	_FOREACHFILE_CODE_
#endif
#ifdef	LIB
#define	_FOREACHFILE_CODE_
#endif

#ifdef	_FOREACHFILE_CODE_

/* Library module source code */

							/* TREE SCANNING */
#ifdef	S_IFLNK				/* system with symbolic links */
#define	LSTAT	lstat
#else	/* S_IFLNK */
#define	LSTAT	Stat
#endif	/* S_IFLNK */

int
is_dirstat(const struct stat *fs) {
	if (!fs) return 0;
	return ((fs->st_mode & S_IFMT) == S_IFDIR);
}

int
is_Dirname(const Fchar *Fn) {
	if (!Fn) return 0;
	struct stat stb;
	if (LSTAT(Fn, &stb) < 0) return 0;
	return ((stb.st_mode & S_IFMT) == S_IFDIR);
}

int
is_Admin_Dirname(const Fchar *Fn) {
	if (!Fn) return 0;
	return	Fnamecmp(Fn, str2Fname(".")) == 0
	||	Fnamecmp(Fn, str2Fname("..")) == 0;
}

static void
error(	const Fchar *Fn, const char *msg,
	int (*proc_before)(const Fchar *, const char *, const struct stat *),
	void (*proc_after)(const Fchar *, const char *, const struct stat *)
) {
	if (proc_before) {
		(void)(*proc_before)(Fn, msg, 0);
	} else {
		(*proc_after)(Fn, msg, 0);
	}
}

static void do_dir(	/* mutually recursive with do_name() */
	Fchar *Fn,
	int (*proc_before)(const Fchar *, const char *, const struct stat *),
	void (*proc_after)(const Fchar *, const char *, const struct stat *)
);

static void
do_name(Fchar *Fn,
	int (*proc_before)(const Fchar *, const char *, const struct stat *),
	void (*proc_after)(const Fchar *, const char *, const struct stat *),
	int is_top_level
) {
	/* examine Fn */
	struct stat fs;
	if (LSTAT(Fn, &fs) < 0) {
		error(Fn, strerror(errno), proc_before, proc_after);
		return;
	}

	/* report on Fn and get possible return code */
	int rc = (proc_before ? (*proc_before)(Fn, (char*)0, &fs) : 1);

	if (	is_dirstat(&fs)
		/* Fn is a directory, so rc may be meaningful */
	&&	(is_top_level || rc != 0)
#ifdef	S_IFLNK
		/* don't follow symbolic links */
	&&	(fs.st_mode & S_IFMT) != S_IFLNK
#endif
	) {	/* we are allowed to recurse */
		do_dir(Fn, proc_before, proc_after);
	}
	if (proc_after) (*proc_after)(Fn, (char*)0, &fs);
}

static void
do_dir(
	Fchar *Fn,
	int (*proc_before)(const Fchar *, const char *, const struct stat *),
	void (*proc_after)(const Fchar *, const char *, const struct stat *)
) {

	/* treat directory */
	Dir_t *dir = Opendir(Fn);
	if (dir == 0) {
		error(Fn, "directory not readable", proc_before, proc_after);
		return;
	}

	/* scan new directory */

	/* append separator */
	int Fn_len = Fnamelen(Fn);
	Fn[Fn_len++] = '/';
	Fn[Fn_len] = '\0';

	/* descend */
	Dirent_t *dent;
	while ((dent = Readdir(dir)) != (Dirent_t *)0) {
		const Fchar *d_name = dent->d_name;
		if (is_Admin_Dirname(d_name)) continue;

		/* append name */
		Fnamecat(Fn, d_name);
		do_name(Fn, proc_before, proc_after, 0);
		/* remove appended name*/
		Fn[Fn_len] = '\0';
	}
	/* remove appended separator*/
	Fn[--Fn_len] = '\0';
	Closedir(dir);
}

static Fchar MSDOS_sep = (Fchar)'\\';
static Fchar UNIX_sep = (Fchar)'/';

static void
clean_name(Fchar *Fn) {
	/* remove a trailing separator */
	int Fn_len = Fnamelen(Fn);
	if (	Fn_len > 1
	&&	(Fn[Fn_len-1] == MSDOS_sep || Fn[Fn_len-1] == UNIX_sep)
	) {
		Fn[Fn_len-1] = '\0';
	}
}

							/* THE ENTRIES */
void
ForEachFile(
	const Fchar *Fname,
	int (*proc_before)(const Fchar *, const char *, const struct stat *),
	void (*proc_after)(const Fchar *, const char *, const struct stat *)
) {
	/* sanity checks */
	if (!Fname || !Fname[0]) return;
	if (!proc_before && !proc_after) return;

	/* make a modifiable copy of Fname */
	Fchar Fn[MAX_PATH_NAME_LENGTH];
	Fnamecpy(Fn, Fname);
	clean_name(Fn);

	/* top level */
	do_name(Fn, proc_before, proc_after, 1);
}

/* End library module source code */
#endif	/* _FOREACHFILE_CODE_ */

#ifdef	lint
static void
satisfy_lint(void *x) {
	(void)is_dirstat(0);
	(void)is_Dirname(0);
	(void)is_Admin_Dirname(0);
	ForEachFile(0, 0, 0);
	satisfy_lint(x);
}
#endif	/* lint */
